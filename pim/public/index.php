<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;
use Paheko\Users\Session;
use PDO;

/*
if ($_SERVER['REQUEST_URI'] == '/.well-known/caldav')
{
	header('Location: /calendars/');
	exit;
}
elseif ($_SERVER['REQUEST_URI'] == '/.well-known/carddav')
{
	header('Location: /addressbooks/');
	exit;
}*/

class PIMSession extends Session
{
	// Use a different session name so that someone cannot access the admin
	// with a cookie stolen from the WebDAV client
	protected $cookie_name = 'pkopim';
}

$session = PIMSession::getInstance();

if (!$session->isLogged()) {
	if (empty($_SERVER['PHP_AUTH_USER'])
		|| empty($_SERVER['PHP_AUTH_PW'])
		|| !$session->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
	) {
		header('WWW-Authenticate: Basic realm="Please login"', true);
		header('HTTP/1.0 401 Unauthorized', true);
		exit;
	}
}

PIM::verifyAccess($session);
PIM::enableDependencies();

$user = $session->user();

$authBackend = new \Sabre\DAV\Auth\Backend\BasicCallBack(fn () => true);

$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE principals (
	id INTEGER PRIMARY KEY ASC NOT NULL,
	uri TEXT NOT NULL,
	email TEXT,
	displayname TEXT,
	UNIQUE(uri)
);
CREATE TABLE groupmembers (
	id INTEGER PRIMARY KEY ASC NOT NULL,
	principal_id INTEGER NOT NULL,
	member_id INTEGER NOT NULL,
	UNIQUE(principal_id, member_id)
);
');

$st = $pdo->prepare('INSERT INTO principals VALUES (1, ?, ?, ?);');
$st->execute(['principals/' . $user->id(), $user->email(), $user->name()]);

$principalBackend = new \Sabre\DAVACL\PrincipalBackend\PDO($pdo);

// Custom backends
$calendarBackend = new CalDAV($user->id);
$carddavBackend = new CardDAV($user->id);

// Directory tree
$tree = [
	new \Sabre\DAVACL\PrincipalCollection($principalBackend),
	new \Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
	new \Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend),
];

// The object tree needs in turn to be passed to the server class
$server = new \Sabre\DAV\Server($tree);

// You are highly encouraged to set your WebDAV server base url. Without it,
// SabreDAV will guess, but the guess is not always correct. Putting the
// server on the root of the domain will improve compatibility.
$server->setBaseUri('/p/pim/');

// Authentication plugin
$authPlugin = new \Sabre\DAV\Auth\Plugin($authBackend);
$server->addPlugin($authPlugin);

// CalDAV plugin
$server->addPlugin(new \Sabre\CalDAV\Plugin);
$server->addPlugin(new \Sabre\CardDAV\Plugin);
$server->addPlugin(new \Sabre\DAV\Sync\Plugin);

// ACL plugin
$server->addPlugin(new \Sabre\DAVACL\Plugin);

// Support for html frontend
$server->addPlugin(new \Sabre\DAV\Browser\Plugin(false));

$server->exec();

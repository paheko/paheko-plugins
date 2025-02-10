<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;
use Paheko\Users\Users;
use PDO;
use KD2\ErrorManager;

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

PIM::enableDependencies();

class PIMServer extends \Sabre\DAV\Server
{
	/**
	 * Override start method to actually display and report exceptions correctly
	 */
	public function start()
	{
		try {
			$this->httpResponse->setHTTPVersion($this->httpRequest->getHTTPVersion());

			// Setting the base url
			$this->httpRequest->setBaseUrl($this->getBaseUri());
			$this->invokeMethod($this->httpRequest, $this->httpResponse);
			//file_put_contents(__DIR__ . '/dav.log', (string)$this->httpRequest . "\n\n" . (string)$this->httpResponse . "\n\n", FILE_APPEND);
		} catch (\Throwable $e) {
			// Ignore client errors
			if (!preg_match('/An If-Match header was specified/', $e->getMessage())) {
				if (\Paheko\SHOW_ERRORS) {
					throw $e;
				}

				$id = ErrorManager::reportExceptionSilent($e);
			}
			else {
				$id = null;
			}

			http_response_code(500);
			header('Content-Type: application/xml; charset=utf-8', true);
			echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
			echo '<d:error xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">' . PHP_EOL;

			if ($id) {
				printf('<s:message>Internal error (ref: %s)</s:message>', $id) . PHP_EOL;
			}
			else {
				printf('<s:message>%s</s:message>', htmlspecialchars($e->getMessage(), ENT_XML1 | ENT_QUOTES)) . PHP_EOL;
			}

			echo '</d:error>';
		}
	}
}

if (empty($_SERVER['PHP_AUTH_USER'])
	|| empty($_SERVER['PHP_AUTH_PW'])) {
	$message = 'Please login';
}
elseif ($user = Users::getWithLoginAndPassword($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
	$message = null;
}
else {
	$message = 'Invalid login';
}

if ($message !== null) {
	header(sprintf('WWW-Authenticate: Basic realm="%s"', $message), true);
	header('HTTP/1.0 401 Unauthorized', true);
	return;
}

if (!$user->id) {
	throw new UserException('Seuls les membres peuvent accéder à cette extension', 403);
}

// Sabre/DAV expects to find logged-user name in PHP_AUTH_USER
$_SERVER['REMOTE_USER'] = $user->id();
$_SERVER['PHP_AUTH_USER'] = $user->id();
$_SERVER['PHP_AUTH_PW'] = '*secret*';

$authBackend = new \Sabre\DAV\Auth\Backend\BasicCallBack(fn () => true);

// CalDAV/CardDAV do require principal, and it's easier to fake it
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
$server = new PIMServer($tree);
$server->debugExceptions = true;

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

// Debug frontend
$server->addPlugin(new \Sabre\DAV\Browser\Plugin(false));

$server->start();

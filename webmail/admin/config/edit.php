<?php

namespace Paheko;

use Paheko\Plugin\Webmail\Accounts;
use Paheko\Plugin\Webmail\Entities\Account;
use Paheko\Users\Users;

require_once __DIR__ . '/_inc.php';

if (isset($_GET['id'])) {
	$account = Accounts::get((int)$_GET['id']);
}
else {
	$account = Accounts::create();
}

if (!$account) {
	throw new UserException('Ce compte n\'existe plus');
}

$csrf_key = 'webmail_account';

$form->runIf('save', function () use ($account) {
	if (!$account->exists() && (empty($_POST['password']) || !trim($_POST['password']))) {
		throw new UserException('Le mot de passe doit être renseigné');
	}

	$account->importForm();

	if (!empty($_POST['password'])) {
		$account->setPassword(trim($_POST['password']));
	}

	$account->save();
}, $csrf_key, '!p/webmail/config/');

$title = $account->exists() ? 'Configurer l\'adresse' : 'Configurer une nouvelle adresse';

$tpl->assign('security_options', Account::SECURITY_OPTIONS);
$tpl->assign('default_imap_port', Account::DEFAULT_IMAP_PORT);
$tpl->assign('default_smtp_port', Account::DEFAULT_SMTP_PORT);

$account_user = isset($account->id_user) ? [$account->id_user => Users::getName($account->id_user)] : null;

$tpl->assign(compact('account', 'csrf_key', 'title', 'account_user'));

$tpl->display(PLUGIN_ROOT . '/templates/config/edit.tpl');

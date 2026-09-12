<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Methods;
use Paheko\Plugin\Caisse\Tabs;

use Paheko\Email\Emails;
use Paheko\Users\Users;
use Paheko\UserTemplate\UserTemplate;

require __DIR__ . '/_inc.php';

$tab = Tabs::get((int)qg('tab'));

if (!$tab) {
	throw new UserException('La note sélectionnée n\'existe pas ou plus.');
}

function get_receipt($tab)
{
	$items = array_map(fn ($a) => $a->asArray(), $tab->listItems());
	$payments = $tab->listPayments();
	$remainder = $tab->getRemainder();

	$has_credit_methods = Methods::hasCreditMethods();
	$user_credit = $has_credit_methods ? $tab->getUserCredit() : null;

	$tpl = new UserTemplate;
	$tpl->setSourcePath(PLUGIN_ROOT . '/templates/invoice.skel');

	$tpl->assignArray(compact('items', 'payments', 'tab', 'remainder', 'has_credit_methods', 'user_credit'));
	return $tpl;
}

if (isset($_GET['send'])) {
	$csrf_key = 'receipt_send';
	$form->runIf('send', function () use ($tab) {
		$_GET['print'] = 'pdf';
		$r = get_receipt($tab);
		Emails::queue(Emails::CONTEXT_PRIVATE, [trim($_POST['to'])], null, trim($_POST['subject']), trim($_POST['body']), [$r->fetchAsAttachment()]);
	}, $csrf_key, Utils::getSelfURI(['send' => 'done', 'tab' => $tab->id()]));

	$user = null;

	if ($tab->user_id) {
		$user = Users::get($tab->user_id);
	}

	$tpl->assign([
		'name'    => $tab->name,
		'email'   => $user ? $user->email() : null,
		'csrf_key' => $csrf_key,
	]);

	$tpl->display(PLUGIN_ROOT . '/templates/receipt_send.tpl');
}
else {
	$tpl = get_receipt($tab);
	$tpl->serve();
}


<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$ha = HelloAsso::getInstance();

$form = $ha->getForm((int)qg('id'));

if (!$form) {
	throw new UserException('Formulaire inconnu');
}

$page = qg('p') ?? 1;
$per_page = $ha::PER_PAGE;

$list = $ha->listPayments($form, $page, $count);

$restricted = $ha->isTrial();
$restricted_results = $restricted ? $count - $ha::PER_PAGE_TRIAL : null;

$tpl->assign(compact('list', 'form', 'per_page', 'count', 'page', 'restricted', 'restricted_results'));

$tpl->display(PLUGIN_ROOT . '/templates/form.tpl');

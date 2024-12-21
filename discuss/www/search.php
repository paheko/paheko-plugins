<?php

namespace Paheko\Plugin\Discuss;

require_once __DIR__ . '/../init_list.php';

$main = Main::getInstance();

if (empty($_GET['query'])) {
	http_response_code(400);
	die("No parameter passed");
}

$order = isset($_GET['date']) ? 'date' : 'score';
$messages = $main->search(trim($_GET['query']), $order);

$tpl->assign(compact('messages', 'order', 'list'));
$tpl->display('search.tpl');

<?php

namespace Paheko\Plugin\Discuss;

require_once __DIR__ . '/../init_list.php';

if (isset($_POST['auto'])) {
	$list->autoLeave($_POST['auto']);
	http_response_code(200);
	echo 'OK, unsubscribed';
	exit;
}

$error = null;

if (isset($_POST['join']) && trim($_POST['email'] ?? '') !== '') {
	try {
		$list->askJoin(trim($_POST['email']));
		redirect('./?msg=JOIN_OK');
	}
	catch (UserException $e) {
		$error = $e->getMessage();
	}
}

if (isset($_POST['leave']) && trim($_POST['email'] ?? '') !== '') {
	try {
		$list->confirmLeave(trim($_POST['email']));
		redirect('./?msg=LEAVE_OK');
		exit;
	}
	catch (UserException $e) {
		$error = $e->getMessage();
	}
}

$show_threads = false;
$threads = [];
$per_page = 50;

if ($list->config->access === 'open') {
	$show_threads = true;
}
elseif ($list->config->access === 'restricted' && $logged_user) {
	$show_threads = true;
}
elseif ($list->config->access === 'closed' && $logged_user && $logged_user->isModerator()) {
	$show_threads = true;
}

if ($show_threads) {
	$threads_count = $list->countThreads();
	$page = intval($_GET['page'] ?? 1);

	if (($page - 1) * $per_page > $threads_count) {
		$page = floor($total / $per_page);
	}

	$start = ($page - 1) * $per_page;

	$threads = $list->listThreads($start, $per_page, $logged_user);
}

$tpl->assign(compact('list', 'error', 'show_threads', 'threads', 'threads_count', 'per_page'));
$tpl->display('index.tpl');

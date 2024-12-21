<?php

namespace Paheko\Plugin\Discuss;

require_once __DIR__ . '/../init_list.php';

if (empty($_GET['id']) && empty($_GET['uri'])) {
	http_response_code(400);
	die("No parameter passed");
}

if (!empty($_GET['id'])) {
	$thread = $list->getThreadByID((int)$_GET['id']);
}
else {
	$thread = $list->getThreadByURI($_GET['uri']);
}

if (!$thread) {
	http_response_code(404);
	die("Thread not found");
}

$messages = $thread->iterateMessages();

$tpl->assign(compact('thread', 'messages', 'can_reply'));
$tpl->display('thread.tpl');

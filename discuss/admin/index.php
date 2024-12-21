<?php

namespace Paheko\Plugin\Discuss;

require_once __DIR__ . '/_inc.php';

$error = null;

if (!empty($_GET['reset']) && ($user = Users::get((int) $_GET['reset']))) {
	$user->resetBounceCount();
	$user->save();
	redirect('./');
}
elseif (!empty($_POST['add'])) {
	try {
		$count = Users::subscribeFromString($_POST['add'], !empty($_POST['send_msg']));
		redirect('./?added=' . $count);
	}
	catch (UserException $e) {
		$error = $e->getMessage();
	}
}
elseif (!empty($_POST['remove'])) {
	$count = Users::removeFromArray($_POST['checked'], !empty($_POST['send_msg']));
	redirect('./?removed=' . $count);
}
elseif (!empty($_POST['set_moderator'])) {
	Users::setModeratorFlag($_POST['checked'], true);
	redirect('./');
}
elseif (!empty($_POST['unset_moderator'])) {
	Users::setModeratorFlag($_POST['checked'], false);
	redirect('./');
}

$members = Users::list();
$members_count = Users::count();

Template::display('admin/index.tpl', compact('members', 'members_count'));

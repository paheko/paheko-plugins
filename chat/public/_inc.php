<?php

namespace Paheko\Plugin\Chat;

use Paheko\Plugin\Chat\Chat;
use Paheko\Plugin\Chat\Entities\Channel;
use Paheko\Plugin\Chat\Entities\User;
use Paheko\Users\Session;
use Paheko\Template;
use const Paheko\PLUGIN_URL;

function chat_avatar(array $params): string
{
	$avatar_url = '/user/avatar/';
	$object = $params['object'];

	if ($object instanceof User) {
		$id = $object->id;
		$id_user = $object->id_user;
		$name = $object->name;
	}
	else {
		$id = $object->id_user;
		$id_user = $object->real_user_id;
		$name = $object->user_name;
	}

	if ($id_user) {
		$avatar_url .= $id_user;
	}
	else {
		$avatar_url .= 'chat_' . md5($name);
	}

	$out = '<img src="' . $avatar_url . '" />';
	$link = '%s';

	if (isset($params['pm'])) {
		$href = sprintf('./?id=%d&with=%d', $object->id_channel, $id);
		$link = sprintf('<a href="%s" target="_parent">%%s</a>', htmlspecialchars($href), $out);
	}

	$out = '<figure class="chat-avatar">' . $out . '</figure>';

	if (!empty($params['name'])) {
		$out .= htmlspecialchars($name);
	}

	if (!empty($params['online'])) {
		$out .= ' ' . user_online(['user' => $object]);
	}

	$out = sprintf($link, $out);
	return $out;
}

function user_online(array $params): string
{
	if ($params['user']->isOnline()) {
		return '<span title="En ligne" class="chat-status online">[on]</span>';
	}
	else {
		return '<span title="Déconnecté" class="chat-status offline">[off]</span>';
	}
}

function chat_message_format(string $text): string
{
	$text = htmlspecialchars($text);
	$text = preg_replace('/\*\*((?:(?!\*\*).)+?)\*\*/s', '<b>$1</b>', $text);
	$text = preg_replace('/_([^_]+?)_/s', '<i>$1</i>', $text);
	$text = preg_replace('/`([^`]+?)`/s', '<code>$1</code>', $text);
	$text = preg_replace('/~~((?:(?!~~).)+?)~~/s', '<del>$1</del>', $text);
	$text = preg_replace('/((?:(?!~~).)+?)~~/s', '<del>$1</del>', $text);
	$text = preg_replace('/^>(.*)$/m', '<blockquote>$1</blockquote>', $text);
	$text = preg_replace(';(?<!")https?://[^<\s]+(?!");', '<a href="$0" target="_blank">$0</a>', $text);
	return $text;
}

$tpl = Template::getInstance();

$tpl->assign('custom_css', PLUGIN_URL . 'chat.css');

$tpl->register_function('chat_avatar', __NAMESPACE__ . '\chat_avatar');
$tpl->register_function('user_online', __NAMESPACE__ . '\user_online');

function get_channel(): Channel
{
	$session = Session::getInstance();
	$channel = null;

	if ($_GET['id'] ?? null) {
		$channel = Chat::getChannel((int)$_GET['id'], $session);
	}

	if (!$channel) {
		$channel = Chat::getFallbackChannel($session);
	}

	if (!$channel) {
		if ($session->isLogged()) {
			Utils::redirect('!p/chat/edit.php');
		}

		throw new ValidationException('No valid channel provided', 400);
	}

	return $channel;
}

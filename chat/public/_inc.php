<?php

namespace Paheko\Plugin\Chat;

use Paheko\Plugin\Chat\Chat;
use Paheko\Plugin\Chat\Entities\Channel;
use Paheko\Plugin\Chat\Entities\User;
use Paheko\Users\Session;
use Paheko\Template;
use Paheko\UserTemplate\CommonFunctions;
use Paheko\UserTemplate\CommonModifiers;
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

	if (isset($params['direct'])) {
		$href = sprintf('./?with=%d', $id);
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
	$text = nl2br($text);
	return $text;
}

function chat_message_html($message, User $me, ?string &$current_day, ?string &$current_user): string
{
	$date = date('Ymd', $message->added);
	$out = '';

	if ($current_day !== $date && $current_day != -1) {
		$current_day = $date;
		$out .= sprintf('<h4 class="ruler">%s</h4>', CommonModifiers::date_long($message->added));
	}

	$out .= sprintf('<article data-date="%d" data-user="%d" data-id="%d" id="msg-%3$d">', $date, $message->id_user, $message->id);

	if ($current_user != $message->id_user && $current_user != -1) {
		$current_user = $message->id_user;

		$out .= sprintf('
			<header>
				%s
				<time>%s</time>
			</header>
			<div class="line">
				<div class="web-content">%s</div>
			</div>',
			chat_avatar(['direct' => true, 'object' => $message, 'name' => true]),
			date('H:i', $message->added),
			chat_message_format($message->content)
		);
	}
	else {
		$out .= sprintf('
			<div class="line">
				<time>%s</time>
				<div class="web-content">%s</div>
			</div>',
			date('H:i', $message->added),
			chat_message_format($message->content)
		);
	}

	$out .= '<footer>';

	if ($message->id_user === $me->id) {
		$out .= CommonFunctions::linkbutton(['shape' => 'edit', 'title' => 'Éditer', 'target' => '_dialog', 'href' => 'edit_message.php?id=' . $message->id, 'label' => '']);
		$out .= CommonFunctions::linkbutton(['shape' => 'delete', 'title' => 'Supprimer', 'target' => '_dialog', 'href' => 'delete_message.php?id=' . $message->id, 'label' => '']);
	}

	// FIXME
	//$out .= CommonFunctions::linkbutton(['shape' => 'link', 'title' => 'Permalien', 'target' => '_blank', 'href' => sprintf('./?id=%d&focus=%d', $message->id_channel, $message->id)]);

	$out .= CommonFunctions::button(['shape' => 'chat', 'title' => 'Répondre', 'data-action' => 'reply']);
	$out .= CommonFunctions::button(['shape' => 'smile', 'title' => 'Réaction', 'data-action' => 'react']);

	$out .= '
		</footer>
	</article>';

	return $out;
}

$tpl = Template::getInstance();

$tpl->assign('custom_css', PLUGIN_URL . 'chat.css');

$tpl->register_function('chat_avatar', __NAMESPACE__ . '\chat_avatar');
$tpl->register_modifier('chat_message_html', __NAMESPACE__ . '\chat_message_html');


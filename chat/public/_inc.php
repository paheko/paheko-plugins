<?php

namespace Paheko\Plugin\Chat;

use Paheko\Plugin\Chat\Chat;
use Paheko\Plugin\Chat\Entities\Channel;
use Paheko\Plugin\Chat\Entities\Message;
use Paheko\Plugin\Chat\Entities\User;

use Paheko\Files\Files;
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

	if (!empty($params['name']) && $name) {
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

function chat_message_format(string $text, ?User $user): string
{
	$text = htmlspecialchars($text);
	$text = preg_replace_callback('!\[(.*)\]\((.*)\)!U', function ($m) {
		$label = $m[1] ?: $m[2];
		$url = $m[2];
		return sprintf('<a href="%s" rel="noopener noreferrer">%s</a>', $url, $label);
	}, $text);
	$text = preg_replace('/\*\*((?:(?!\*\*).)+?)\*\*/s', '<b>$1</b>', $text);
	$text = preg_replace('/\*([^\*]+?)\*/s', '<i>$1</i>', $text);
	$text = preg_replace_callback('/`+([^`]+?)`+/s', fn($match) => '<code>' . str_replace("\n", '<br />', trim($match[1], "\n")) . '</code>', $text);
	$text = preg_replace('/~~((?:(?!~~).)+?)~~/s', '<s>$1</s>', $text);
	$text = preg_replace('/^&gt;(.*)$/m', '<blockquote>$1</blockquote>', $text);
	$text = preg_replace('/^\*\s+(.*)$/m', '<li>$1', $text);
	$text = preg_replace(';(?<!")https?://[^<\s]+(?!");', '<a href="$0" target="_blank">$0</a>', $text);
	$text = str_replace('%encoded_username%', rawurlencode($user->name ?? ''), $text);
	$text = nl2br($text);
	$text = preg_replace('!</blockquote>\s*<br\s+/?>\s*<blockquote>!', '<br />', $text);
	return sprintf('<div class="web-content">%s</div>', $text);
}

function chat_message_file(int $id): string
{
	$file = Files::getByID($id);

	if (!$file) {
		return '<p class="deleted">Ce fichier a été supprimé.</p>';
	}

	$session = Session::getInstance();

	if (!$file->canRead($session)) {
		return '<p class="deleted">Vous n\'avez pas accès à ce fichier.</p>';
	}

	if ($file->mime === 'audio/ogg') {
		return sprintf('<audio autostart="0" controls="true" preload="none" src="%s" />', $file->url());
	}

	if ($file->isImage()) {
		return '<figure class="image">' . $file->link($session, '500px', true) . '</figure>';
	}

	return sprintf('
		<figure class="file">
			<span class="thumb">%s</span>
			<span>
			<figcaption>
				%s
			</figcaption>
			<span class="actions">
				%s
			</span>
			</span>
		</figure>',
		$file->link($session, '150px', true),
		$file->link($session),
		CommonFunctions::linkbutton(['shape' => 'download', 'href' => $file->url(true), 'target' => '_blank', 'label' => 'Télécharger'])
	);
}

function chat_message_html($message, User $me, bool &$first = false): string
{
	static $is_admin = Session::getInstance()->canAccess(Session::SECTION_USERS, Session::ACCESS_ADMIN);
	$date = date('Ymd', $message->added);
	$out = '';

	if ($first || !$message->previous_added || $date != date('Ymd', $message->previous_added)) {
		$out .= sprintf('<h4 class="ruler">%s</h4>', CommonModifiers::date_long($message->added));
	}

	$out .= sprintf('<article data-date="%d" data-user="%d" data-id="%d" id="msg-%3$d">', $date, $message->id_user, $message->id);

	if ($message->type === Message::TYPE_TEXT) {
		$content = chat_message_format($message->content, $me);
	}
	elseif ($message->type === Message::TYPE_COMMENT) {
		$content = sprintf('<div class="comment">%s</div>', chat_message_format('… ' . $message->content, $me));
	}
	elseif ($message->type === Message::TYPE_FILE) {
		$content = chat_message_file($message->id_file);
	}
	elseif ($message->type === Message::TYPE_DELETED) {
		$content = '<p class="deleted">Ce message a été supprimé.</p>';
	}

	if ($first || !$message->previous_user_id || $message->previous_user_id != $message->id_user) {
		$out .= sprintf('
			<header>
				%s
				<time>%s</time>
			</header>
			<div class="line">
				%s
			</div>',
			chat_avatar(['direct' => true, 'object' => $message, 'name' => true]),
			date('H:i', $message->added),
			$content
		);
	}
	else {
		$out .= sprintf('
			<div class="line">
				<time>%s</time>
				%s
			</div>',
			date('H:i', $message->added),
			$content
		);
	}

	if (!empty($message->reactions)) {
		$out .= '<nav class="reactions">';

		if (is_string($message->reactions)) {
			$message->reactions = json_decode($message->reactions, true);
		}

		foreach ($message->reactions as $emoji => $users) {
			$users_names = implode("\n", Chat::getUsersNames($users));
			$out .= sprintf('<button title="%s" %s data-emoji="%3$s"><b>%s</b> <span>%d</span></button>',
				$users_names,
				in_array($me->id(), $users) ? 'class="me"' : '',
				$emoji,
				count($users)
			);
		}

		$out .= '</nav>';
	}


	if ($message->type !== Message::TYPE_DELETED) {
		$out .= '<footer>';
		// TODO
		$out .= CommonFunctions::linkbutton(['shape' => 'link', 'title' => 'Permalien', 'label' => '', 'target' => '_blank', 'href' => sprintf('./?id=%d&focus=%d#msg-%2$d', $message->id_channel, $message->id)]);

		if ($message->id_user === $me->id || $is_admin) {
			if ($message->type === Message::TYPE_TEXT) {
				// TODO
				//$out .= CommonFunctions::button(['shape' => 'edit', 'title' => 'Éditer', 'data-action' => 'edit',]);
			}

			$out .= CommonFunctions::button(['shape' => 'delete', 'title' => 'Supprimer', 'data-action' => 'delete']);
		}

		//$out .= CommonFunctions::button(['shape' => 'chat', 'title' => 'Répondre', 'data-action' => 'reply']);
		$out .= CommonFunctions::button(['shape' => 'smile', 'title' => 'Réaction', 'data-action' => 'react']);
		$out .= '<button class="react">👍</button>';
		$out .= '<button class="react">❤️</button>';
		$out .= '</footer>';
	}

	$out .= '
	</article>';

	$first = false;

	return $out;
}

$tpl = Template::getInstance();

$tpl->assign('custom_css', PLUGIN_URL . 'chat.css');

$tpl->register_function('chat_avatar', __NAMESPACE__ . '\chat_avatar');
$tpl->register_modifier('chat_message_html', __NAMESPACE__ . '\chat_message_html');


<?php

namespace Paheko\Plugin\Discuss\Entities;

use Paheko\Entity;

class Forum extends Entity
{
	const TABLE = 'plugin_discuss_forums';

	protected ?int $id = null;
	protected string $uri;
	protected string $title;
	protected string $language = 'fr';
	protected ?string $description;

	/**
	 * closed = "Nobody can subscribe, only moderators can add new members"
	 * restricted = "Subscription requests have to approved by a moderator"
	 * open = "Everyone can subscribe freely"
	 */
	protected string $subscribe_permission = self::OPEN;

	/**
	 * closed = "Only moderators can post"
	 * restricted = "Only registered users and moderators can post"
	 * open = "Everyone can post (public)"
	 */
	protected string $post_permission = self::CLOSED;

	/**
	 * closed = "Only moderators can read archives"
	 * restricted = "Only registered users and moderators can read archives"
	 * open = "Everyone can read archives (public)"
	 */
	protected string $archives_permission = self::CLOSED;

	/**
	 * closed = "Only moderators can send attachments"
	 * restricted = "Only registered users and moderators can send attachments"
	 * open = "Everyone can send attachments"
	 * If someone doesn't have the right to send an attachment, it will just be removed.
	 */
	protected bool $attachment_permission = self::CLOSED;

	protected ?string $email;
	protected bool $disable_archives = false;
	protected bool $verify_messages = false;
	protected bool $encrypt_messages = false;

	protected ?string $template_footer;
	protected ?string $template_welcome;
	protected ?string $template_goodbye;

	protected bool $delete_forbidden_attachments = false;
	protected bool $resize_images = true;
	protected int $max_attachment_size = 3*1024*1024;

	const OPEN = 'open';
	const CLOSED = 'closed';
	const RESTRICTED = 'restricted';

	// Maximum message size (including text+any attachment)
	const MAX_MESSAGE_SIZE = 25*1024*1024; // 25 MiB

	const ALLOWED_ATTACHMENT_TYPES = [
		'svg'  => 'image/svg+xml',
		'png'  => 'image/png',
		'jpeg' => 'image/jpeg',
		'jpg'  => 'image/jpeg',
		'gif'  => 'image/gif',
		'webp' => 'image/webp',
		'pdf'  => 'application/pdf',
		'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
		'odt'  => 'application/vnd.oasis.opendocument.text',
		'odp'  => 'application/vnd.oasis.opendocument.presentation',
		'md'   => 'text/plain',
		'txt'  => 'text/plain',
		'html' => 'text/html',
		'htm'  => 'text/html',
		'json' => 'application/json',
		'js'   => 'text/javascript',
		'css'  => 'text/css',
		'csv'  => 'text/csv',
		'doc'  => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xls'  => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'ppt'  => 'application/vnd.ms-powerpoint',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'mp3'  => 'audio/mpeg',
		'ics'  => 'text/calendar',
		'diff' => 'text/x-diff',
		'patch'=> 'text/x-patch',
		'asc'  => 'application/pgp-signature',
		'bundle' => '',
	];

	// No more than MAX_BOUNCES before stopping sending emails to this address
	const MAX_BOUNCES = 50;

	// Reject messages with a spam score greater than this
	const REJECT_SPAM_SCORE = 20;


	public function isMember(string $address)
	{
		$db = EntityManager::getInstance(User::class)->db();
		return $db->test(User::TABLE, 'email = ?', $address);
	}

	public function listModerators(): array
	{
		$st = self::PDO()->prepare('SELECT id, address FROM lists_members WHERE list_id = ? AND moderator = 1;');
		$st->execute([(int)$list_id]);
		return $st->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	public function search(string $query, string $order = 'score')
	{
		$db = EntityManager::getInstance(Thread::class)->db();
		return $db->iterate(sprintf('SELECT
			s.message_id AS id, s.thread_id, s.subject, s.content, t.uri, t.subject,
			m.from_name
			snippet(s, \'<mark>\', \'</mark>\', \'…\', 2, -30) AS snippet,
			rank(matchinfo(s), 0, 0, 1.0, 1.0) AS points
			FROM search s
			INNER JOIN threads t ON t.id = s.thread_id
			INNER JOIN messages m ON m.id = s.message_id
			WHERE s MATCH ?
			ORDER BY %s DESC
			LIMIT 0,50;', $order), $query);
	}

	public function listThreads(int $start = 0, int $limit = 500): array
	{
		$em = EntityManager::getInstance(Thread::class);
		return $em->all(sprintf('SELECT * FROM @TABLE WHERE (status & %d) != %1$d ORDER BY last_update DESC LIMIT %d, %d;',
			Thread::HIDDEN,
			max(0, $start),
			$limit
		));
	}

	public function countThreads(): int
	{
		$em = EntityManager::getInstance(Thread::class);
		$db = $em->db();
		return $db->count(Thread::TABLE);
	}

	static public function updateMembersCLI($list, $send_welcome = false, $send_goodbye = false)
	{
		if (!filter_var($list, FILTER_VALIDATE_EMAIL))
			throw new \RuntimeException('Invalid list address: '.$list);

		list($name, $domain) = explode('@', $list);

		$st = self::PDO()->prepare('SELECT id FROM lists WHERE name = ? AND domain = ?;');
		$st->execute([$name, $domain]);
		$result = $st->fetch(\PDO::FETCH_ASSOC);

		if (empty($result))
		{
			throw new \RuntimeException('Unknown list address: '.$list);
		}

		$id = $result['id'];

		$updated_members = file('php://stdin', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if (empty($updated_members))
		{
			throw new NG_User_Exception('No member list was provided to STDIN');
		}

		$list = new Nano_Mail_List(null, null);

		$all = $list->listMembers($id);

		$remove = [];
		$add = [];

		foreach ($all as &$m)
		{
			if (!in_array($m['address'], $updated_members))
			{
				$remove[] = $m['id'];
			}

			$m = $m['address'];
		}

		foreach ($updated_members as $i=>$line)
		{
			if (!filter_var($line, FILTER_VALIDATE_EMAIL))
			{
				throw new NG_User_Exception('Line '.$i.': "'.$line.'" is not a valid email address.');
			}

			if (!in_array($line, $all))
			{
				$add[] = $line;
			}
		}

		unset($all, $updated_members);

		$list->removeMembers($id, $remove, $send_goodbye);
		$list->addMembers($id, $add, $send_welcome);
		return true;
	}

	static public function receive($to, $from, $reply_to, $message_id)
	{
		$to = current(SMTP::extractEmailAddresses($to));
		$from = current(SMTP::extractEmailAddresses($from));
		$reply_to = current(SMTP::extractEmailAddresses($reply_to));

		if (!empty($reply_to) && preg_match('!\(.*\)!', $reply_to, $match))
			$reply_to = trim(str_replace($match[0], '', $reply_to));

		if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL))
			throw new \RuntimeException('Invalid email address for To: '.$to);

		if (false !== $from && $from !== '' && !filter_var($from, FILTER_VALIDATE_EMAIL))
			throw new \RuntimeException('Invalid email address for From: '.$from);

		if (!$reply_to || !filter_var($reply_to, FILTER_VALIDATE_EMAIL))
			throw new \RuntimeException('Invalid email address for Reply-To: '.$reply_to);

		if (preg_match('/^([a-z0-9_]+)'
			. '(?:-((?:un)?subscribe|join|leave|request|owner|help|bounces)'
			. '(?:-([a-z0-9-]+))?)?@(.+)$/',
			$to, $match))
		{
			$list = self::getList($match[1], $match[4]);

			if (!$list)
			{
				throw new NG_User_Exception('This mailing list is not found.');
			}

			if (empty($from) && $match[2] != 'bounces')
			{
				throw new NG_User_Exception('The message does not have a From: header.');
			}

			if (empty($match[2]))
			{
				return self::post($list, $from, $reply_to, $message_id);
			}

			switch ($match[2])
			{
				case 'subscribe':
				case 'join':
					if (!empty($match[3]))
						return self::confirmJoin($list, $from, $reply_to, $match[3]);
					else
						$return = self::askJoin($list, $from);

						if ($return === -1)
						{
							return self::commandFail($list, $from, null, 'This address is already subscribed to this list.');
						}

						return $return;
				case 'unsubscribe':
				case 'leave':
					return self::confirmLeave($list, $from, $reply_to);
				case 'request': // FIXME handle requests
				case 'help':
					return self::help($list, $from, $reply_to);
				case 'owner':
					return self::forwardToOwner($list, $from);
				case 'bounces':
					return self::bounce($list, $from, $match[3]);
			}

			throw new \RuntimeException('Invalid command.');
		}
		else
		{
			throw new NG_User_Exception('Invalid or unknown address.');
		}
	}

	static protected function bounce($list, $from, $id = null)
	{
		if (empty($id))
		{
			throw new Exception('Invalid bounce message for list.');
		}

		$id = base_convert($id, 36, 10);

		if (!$id)
		{
			throw new Exception('Unknown ID in bounce.');
		}

		require_once NG_ROOT . '/include/kd2fw/Mail_Message.php';
		$msg = new Mail_Message;
		$msg->parse(self::readMessage($list));

		$return = $msg->identifyBounce();
		$delete = false;

		if ($return['type'] == 'autoreply' || $return['type'] == 'genuine') {
			return;
		}
		elseif ($return['type'] == 'complaint') {
			// Unsubscribe user directly if he/she complains
			$delete = true;
		}
		elseif ($return['type'] == 'permanent') {
			// Unsubscribe user directly if he/she is failing permanently
			$delete = true;
		}
		else {
			// Temporary fail
			$st = self::PDO()->prepare('UPDATE lists_members SET stats_bounced = stats_bounced + 1 WHERE list_id = ? AND id = ?;');
			$st->execute([(int) $list['id'], (int) $id]);
		}

		$st = self::PDO()->prepare('SELECT address, stats_bounced FROM lists_members WHERE list_id = ? AND id = ?;');
		$r = $st->execute([(int) $list['id'], (int) $id]);
		$r = $st->fetchObject();

		if (!$r) {
			// Subscriber has already been unsubscribed
			return true;
			//throw new \LogicException(sprintf('Cannot find subscriber for bounce: id=%s/list_id=%s', $id, $list['id']));
		}

		if ($r->stats_bounced >= self::MAX_BOUNCES) {
			$delete = true;
		}

		if ($delete) {
			$st = self::PDO()->prepare('DELETE FROM lists_members WHERE list_id = ? AND id = ?;');
			$st->execute([(int) $list['id'], (int) $id]);
		}

		if ($delete && $return['type'] == 'temporary') {
			$mail = self::template('removed_after_too_many_failed_attempts', $list, ['failed_address' => $r->address]);
			$mail->setHeader('To', $r->address);
			$mail->send();
		}

		if ($delete) {
			$mail = self::template('removed_after_fail', $list, ['failed_address' => $r->address, 'failed_reason' => $return['type'], 'failed_diagnostic' => $return['diagnostic'] ?? '--none--']);

			$mail->setHeader('to', sprintf('"%s list owner" <%s>', $list['name'], $list['admin']));
			$mail->send();
		}

		return true;
	}

	static public function readMessage(?array $list = null)
	{
		if (!is_null(self::$message)) {
			return self::$message;
		}

		self::$message = '';
		$fp = fopen('php://stdin', 'r');
		$read = [$fp];
		$write = $except = null;

		// Check that STDIN has something for us, or die
		if (!stream_select($read, $write, $except, 1)) {
			throw new \RuntimeException('No content was supplied on STDIN');
		}

		$save = false;
		$size = 0;

		while (!feof($fp)) {
			$line = fgets($fp, 8192);

			// Ignore first non-headers lines, eg. "From email@domain.tld Date"
			if (!$save && preg_match('/^[^\s:]+:/', $line)) {
				$save = true;
			}
			elseif (!$save) {
				continue;
			}

			$size += strlen($line);

			// Enforce maximum message size
			if ($size > self::MAX_MESSAGE_SIZE) {
				throw new NG_User_Exception(sprintf(__($list, 'Maximum message size reached: your message should be below %d MiB'), self::MAX_MESSAGE_SIZE / 1024 / 1024));
			}

			self::$message .= $line;
		}

		fclose($fp);

		return self::$message;
	}

	static protected function forwardToOwner($list, $from)
	{
		require_once NG_ROOT . '/include/kd2fw/Mail_Message.php';

		$new = new \KD2\Mail_Message;
		$new->setBody('An email was sent to you, the owner of this mailing list. Please find it below.');
		$new->attachMessage(self::readMessage($list));
		$new->setHeader('from', sprintf('%s-owner@%s', $list['name'], $list['domain']));

		$to = sprintf('"%s list owner" <%s>', $list['name'], $list['admin']);
		$subject = sprintf('A message from %s about the %s list', $from, $list['name']);

		return self::mail($to, $subject, $new->outputBody(), $new->outputHeaders());
	}

	static protected function help($list, $from, $reply_to)
	{
		$text = "Those are the available commands:\n\n";
		$text .= sprintf('Send an email to %s-join@%s to join this list.', $list['name'], $list['domain']);
		$text .= "\n\n";
		$text .= sprintf('Send an email to %s-leave@%s to unsubscribe this list.', $list['name'], $list['domain']);
		$text .= "\n\n";
		$text .= sprintf('Send an email to %s-owner@%s to contact the owner of this list.', $list['name'], $list['domain']);

		$sender = sprintf('%s-request@%s', $list['name'], $list['domain']);
		return self::mail($reply_to, 'Mailing list help', $text, ['From' => $sender]);
	}

	static protected function makeRequestId($list, $address)
	{
		$address = strtolower($address);
		$hash = substr(sha1($list['id'] . $address . MAIL_SECRET), 0, 10);

		$hash = base_convert($hash, 16, 36);

		return $hash;
	}

	static protected function checkRequestId($list, $address, $hash)
	{
		if (!preg_match('!^[a-z0-9]+!', $hash, $match))
		{
			return false;
		}

		$check = self::makeRequestId($list, $address);

		return strcasecmp($check, $hash) === 0;
	}

	static public function askJoin($list, $from)
	{
		if (self::isMember($list['id'], $from))
		{
			return -1;
		}

		if ($list['subscription'] == self::SUBSCRIPTION_CLOSED)
		{
			$subject = sprintf(self::__($list, 'Your subscription to the list %s is denied.'), $list['name']);
			$from = sprintf('%s-bounces@%s', $list['name'], $list['domain']);
			$owner = sprintf('%s-owner@%s', $list['name'], $list['domain']);

			$text = $subject . "\n\n";
			$text.= self::__($list, 'This list is closed, you can\'t subscribe by yourself.') . "\n\n";
			$text.= sprintf(self::__($list, 'Please contact the list owner at %s to be subscribed.'), $owner);

			return self::mail($from, $subject, $text, ['From' => $from]);
		}
		elseif ($list['subscription'] == self::SUBSCRIPTION_REQUEST)
		{
			// FIXME handle subscription requests
			throw new \Exception('FIXME');
			$subject = sprintf(self::__($list, 'Your subscription request was forwarded to the %s list owner'), $list['name']);

			$text = self::__($list, 'This mailing list subscriptions are handled by the list owner.');

			return self::mail($from, $subject, $text, ['From' => $confirm]);
		}
		else
		{
			$id = self::makeRequestId($list, $from);

			$confirm = sprintf('%s-join-%s@%s', $list['name'], $id, $list['domain']);
			$subject = sprintf(self::__($list, 'Please confirm your subscription to the %s list'), $list['name']);

			$text = sprintf(self::__($list, 'To confirm your subscription to the %s list you have to reply to this message or send a message to this address:'), $list['name']) . "\n\n";
			$text.= $confirm . "\n\n";
			$text.= self::__($list, 'If you didn\'t ask to subscribe to this list just ignore this message.');

			return self::mail($from, $subject, $text, ['From' => $confirm]);
		}
	}

	static protected function confirmJoin($list, $from, $reply_to, $hash)
	{
		if (!self::checkRequestId($list, $from, $hash))
		{
			throw new NG_User_Exception('Invalid request hash: '.$hash);
		}

		$st = self::PDO()->prepare('INSERT IGNORE INTO lists_members (list_id, address, stats_since) VALUES (?, ?, NOW());');
		$st->execute([$list['id'], $from]);

		return self::sendWelcome($list, $from);
	}

	static protected function sendWelcome($list, $to)
	{
		$subject = sprintf(self::__($list, 'Welcome to %s'), $list['name']);

		$replace = [
			'%subscriber_address'   =>  $to,
			'%list_address'         =>  sprintf('%s@%s', $list['name'], $list['domain']),
			'%list_name'            =>  $list['name'],
		];

		if (!empty($list['welcome_msg']))
		{
			$text = $list['welcome_msg'];
		}
		else
		{
			$text = self::__($list, "Welcome to %list_name. Your subscriber address is %subscriber_address.");
		}

		$text = strtr($text, $replace);

		$from = sprintf('%s-request@%s', $list['name'], $list['domain']);
		return self::mail($to, $subject, $text, ['From' => $from]);
	}

	static public function confirmLeave($list, $from)
	{
		$st = self::PDO()->prepare('DELETE FROM lists_members WHERE list_id = ? AND address = ?;');
		$st->execute([$list['id'], $from]);

		return self::sendGoodbye($list, $from);
	}

	static public function autoLeave($list, string $id)
	{
		$id = base_convert($id, 36, 10);
		$st = self::PDO()->prepare('SELECT address FROM lists_members WHERE list_id = ? AND id = ?;');
		$st->execute([(int)$list['id'], (int)$id]);
		$from = $st->fetchColumn();

		$st = self::PDO()->prepare('DELETE FROM lists_members WHERE list_id = ? AND id = ?;');
		$st->execute([$list['id'], (int) $id]);

		return self::sendGoodbye($list, $from);
	}

	static protected function sendGoodbye($list, $to)
	{
		$subject = sprintf(self::__($list, 'Goodbye from %s'), $list['name']);
		$from = sprintf('%s-request@%s', $list['name'], $list['domain']);

		$replace = [
			'%subscriber_address'   =>  $from,
			'%list_address'         =>  sprintf('%s@%s', $list['name'], $list['domain']),
			'%list_name'            =>  $list['name'],
		];

		if (!empty($list['goodbye_msg']))
		{
			$text = $list['goodbye_msg'];
		}
		else
		{
			$text = self::__($list, "You are no longer subscribed to %list_name, and no further messages will be sent to you.");
		}

		$text = strtr($text, $replace);

		return self::mail($to, $subject, $text, ['From' => $from]);
	}

	static public function commandFail($list, $from, $reply_to, $message)
	{
		return self::mail($reply_to ?: $from, 'Request error', $message, [
			'From' => sprintf('%s-bounces@%s', $list['name'], $list['domain'])
		]);
	}

	static protected function post($list, $from, $reply_to, $message_id)
	{
		$is_member = self::isMember($list['id'], $from);

		if ($list['status'] == self::STATUS_MEMBERS && !$is_member)
		{
			// FIXME improve message, but don't reply to spammers
			throw new NG_User_Exception(self::__($list, 'Posting to this list is only allowed to members and you are not one of them.'));
		}
		elseif ($list['status'] == self::STATUS_MODERATORS)
		{
			$a = self::PDO()->prepare('SELECT 1 FROM lists_members WHERE list_id = ? AND address = ? AND moderator = 1;');
			$a->execute([$list['id'], $from]);

			if (!$is_member || !$a->fetch())
			{
				throw new NG_User_Exception(self::__($list, 'Posting to this list is only allowed to moderators and you are not one of them.'));
			}
		}

		require_once NG_ROOT . '/include/kd2fw/Mail_Message.php';
		$msg = new \KD2\Mail_Message;
		$raw_message = self::readMessage($list);
		$msg->parse($raw_message);

		$body = $msg->getBody();

		if (!empty($list['footer'])) {
			$body = $msg->removeSignature($body);
		}

		if (!empty($list['delete_forbidden_attachments']) || $list['max_attachment_size'] > -1) {
			$deleted_type = [];
			$deleted_size = [];
			$extensions = $types = null;

			if (!empty($list['delete_forbidden_attachments'])) {
				$extensions = array_map(fn($a) => preg_quote($a, '/'), array_keys(self::ALLOWED_ATTACHMENT_TYPES));
				$extensions = implode('|', $extensions);
				$extensions = sprintf('/\.(?:%s)$/i', $extensions);

				$types = array_unique(self::ALLOWED_ATTACHMENT_TYPES);
			}

			foreach ($msg->getParts() as $pid => $part) {
				if ($list['max_attachment_size'] && strlen($part['content']) > $list['max_attachment_size']) {
					$deleted_size[] = !empty($part['name']) ? $part['name'] : 'Attachment #' . $pid;
					$msg->removePart($pid);
					continue;
				}

				// Check allowed file extensions/types
				if (!empty($list['delete_forbidden_attachments']) && !empty($part['name'])
					&& !preg_match($extensions, $part['name'])) {
					$deleted_type[] = $part['name'];
					$msg->removePart($pid);
					continue;
				}

				// Always allow embedded text
				if (empty($part['name']) && strstr($part['type'], 'text/')) {
					continue;
				}

				if (!empty($list['delete_forbidden_attachments']) && !empty($part['content'])) {
					$finfo = new finfo(FILEINFO_MIME_TYPE);
					$type = $finfo->buffer($part['content']);
					unset($finfo);

					if (!in_array($type, $types)) {
						$name = !empty($part['name']) ? $part['name'] : sprintf(__($list, 'Embedded attachment #%d'), $pid, $part['type']);
						$name .= sprintf(' (%s)', $type);
						$deleted_type[] = $name;
						$msg->removePart($pid);
						continue;
					}
				}
			}

			if (count($deleted_type)) {
				$body .= "\n\n====\n\n" . self::__($list, 'The following files have been deleted as they are forbidden:') . "\n- ";
				$body .= implode("\n- ", $deleted_type);
				$body .= "\n\n" . sprintf(self::__($list, 'This list only allows the following file types: %s'),
					implode(', ', array_keys(self::ALLOWED_ATTACHMENT_TYPES)));
			}

			if (count($deleted_size)) {
				$body .= "\n\n====\n\n" . self::__($list, 'The following files have been deleted as they are too large:') . "\n- ";
				$body .= implode("\n- ", $deleted_size);
				$body .= "\n\n" . sprintf(self::__($list, 'This list only allows attachments up to %s MiB'), round($list['max_attachment_size'] / 1024 / 1024, 1));
			}
		}

		$list_address = sprintf('%s@%s', $list['name'], $list['domain']);

		if ((float)$msg->getHeader('x-spam-score') >= self::REJECT_SPAM_SCORE)
		{
			throw new \NG_User_Exception(__($list, "Your message to was identified as spam."));
		}

		if (stristr($msg->getHeader('list-id'), $list_address))
		{
			throw new \NG_User_Exception("Detected loop on " . $list_address . " list, discarding message.");
		}

		if (stristr($msg->getHeader('x-loop'), $list_address))
		{
			throw new \NG_User_Exception("Detected from loop on " . $list_address . " list, discarding message.");
		}

		$subject = trim($msg->getHeader('subject'));

		if (empty($subject))
		{
			$subject = '[No subject]';
		}

		$subject = preg_replace('/^(?:Re\s*:\s*)+/', 'Re: ', $subject);

		if (!empty($list['subject_tag']) && !stristr($subject, '[' . $list['subject_tag'] . ']'))
		{
			$subject = '[' . $list['subject_tag'] . '] ' . $subject;
		}

		// We don't use the original DKIM signature if it exists
		$msg->removeHeader('DKIM-Signature');
		$msg->removeHeader('Precedence');

		$encoded_id = base_convert($list['id'], 10, 36);
		$unsubscribe_url = ADMIN_URL . 'lists/?l=' . $encoded_id;

		$msg->appendHeaders([
			'subject' => $subject,
			'list-id' => '<' . $list_address . '>',
			'list-unsubscribe' => sprintf('<%s>, <mailto:%s-leave@%s>', $unsubscribe_url, $list['name'], $list['domain']),
			'list-post' => sprintf('<mailto:%s>', $list_address),
			'list-help' => sprintf('<mailto:%s-help@%s>', $list['name'], $list['domain']),
			'X-Loop' => $list_address,
			'Precedence' => 'list',
		]);

		if (!$msg->getHeader('message-id'))
		{
			$msg->setMessageId();
		}

		// List opened to posts from members or anyone = discussion list
		// List opened only to moderators = newsletter, no need to set reply-to
		if ($list['status'] == self::STATUS_OPEN || $list['status'] == self::STATUS_MEMBERS)
		{
			$msg->removeHeader('Reply-To');
			$msg->setHeader('Reply-To', $list_address);
		}
		// Support mailing list: reply to list AND to original poster
		elseif ($list['status'] == self::STATUS_SUPPORT) {
			$msg->removeHeader('Reply-To');
			$msg->setHeader('Reply-To', $from . ', ' . $list_address);
		}

		if (!empty($list['footer']))
		{
			$body .= "\n\n-- \n";
			$body .= $list['footer'];
		}

		$msg->setBody($body);

		// Get domain for DMARC checks
		$real_from = \KD2\SMTP::extractEmailAddresses($from);
		$real_from = current($real_from);
		$domain = substr($real_from, strrpos($real_from, '@')+1);

		// Fetch DMARC policy
		$st = self::PDO()->prepare('SELECT dmarc_reject FROM lists_domains_policies WHERE domain = ? AND last_update >= NOW() - INTERVAL 1 MONTH;');
		$st->execute([$domain]);
		$reject = $st->fetchColumn();

		// this will be either "1" or "0" if it comes from mysql, false if not found
		if ($reject === false)
		{
			$dmarc = dns_get_record('_dmarc.' . $domain, DNS_TXT);

			if (!empty($dmarc[0]['txt']) && preg_match('/;\s*p=(?:reject|quarantine)/i', $dmarc[0]['txt']))
			{
				$reject = true;
			}

			$st = self::PDO()->prepare('REPLACE INTO lists_domains_policies (domain, last_update, dmarc_reject) VALUES (?, NOW(), ?);');
			$st->execute([$domain, (int)$reject]);
		}

		// If sender policy is to reject, then we need to rewrite the from header
		if ($reject)
		{
			$msg->setHeader('From', sprintf('"%s via %s" <%s>', str_replace('@', ' at ', $real_from), $list['name'], $list_address));
			$msg->setHeader('Sender', $list_address);
			$msg->setHeader('X-Original-List-Sender', $real_from);

			// Public mailing list
			if ($list['status'] != self::STATUS_MODERATORS)
			{
				$reply_to = $list_address . ', ' . $real_from;

				$msg->removeHeader('Reply-To');
				$msg->setHeader('Reply-To', $reply_to);
			}
		}

		$st = self::PDO()->prepare('SELECT address, id FROM lists_members WHERE list_id = ?
			AND stats_bounced < '.self::MAX_BOUNCES.' AND receive = 1;');
		$st->execute([$list['id']]);

		$smtp = new \KD2\SMTP('localhost', 25);

		while ($row = $st->fetch(\PDO::FETCH_NUM))
		{
			// FIXME: send everything with multiple RCPT TO in one SMTP session
			// (that means parsing the bounce message instead to extract the recipient
			// of using a unique bounce address per recipient)
			$encoded_id = base_convert($row[1], 10, 36);
			$bounce = sprintf('%s-bounces-%s@%s', $list['name'], $encoded_id, $list['domain']);
			$msg->setHeader('errors-to', '<' . $bounce . '>');
			$msg->setHeader('return-path', '<' . $bounce . '>');
			$msg->setHeader('list-unsubscribe-post', 'auto=' . $encoded_id);

			$smtp->rawSend($bounce, $row[0], $msg->output());
		}

		$smtp->disconnect();

		if (!empty($list['archive']))
		{
			self::archive($list, $msg, $raw_message, true);
		}

		$st = self::PDO()->prepare('UPDATE lists_members SET stats_posts = stats_posts + 1
			WHERE list_id = ?  AND address = ?;');
		$st->execute([$list['id'], $from]);

		return true;
	}

	static public function archive($list, $message, $original, $now = false)
	{
		$id = $message->getMessageId();
		$in_reply = $message->getInReplyTo();
		$references = $message->getReferences();
		$level = 0;
		$parent = null;
		$topic = null;

		if (trim($id) !== '')
		{
			$st = self::PDO()->prepare('SELECT 1 FROM lists_archives WHERE message_id = ? LIMIT 1;');
			$st->execute([$id]);

			if ($st->fetch())
			{
				// Message already in table
				return true;
			}
		}

		if (empty($in_reply) && !empty($references))
		{
			$in_reply = current($references);
		}

		if (!empty($in_reply))
		{
			$st = self::PDO()->prepare(sprintf('SELECT id, parent_id, topic_id, level
				FROM lists_archives WHERE message_id = ? AND list_id = %d LIMIT 1;', $list['id']));
			$st->execute([$in_reply]);

			if ($row = $st->fetch(PDO::FETCH_ASSOC))
			{
				$level = $row['level'] + 1;
				$parent = $row['id'];

				if (!empty($row['topic_id']))
					$topic = $row['topic_id'];
				elseif (!empty($row['parent_id']))
					$topic = $row['parent_id'];
				else
					$topic = $row['id'];
			}
		}

		$date = false;

		if (!$now && $message->getHeader('date'))
		{
			$date = strtotime($message->getHeader('date'));

			if ($date)
			{
				$date = 'FROM_UNIXTIME("' . $date . '")';
			}
		}

		if (!$date)
		{
			$date = 'NOW()';
		}

		$subject = preg_replace('/^(?:Re\s*:\s*)+/', '', $message->getHeader('subject'));

		// Remove subject
		if (!empty($list['subject_tag']))
		{
			$subject = str_replace('[' . $list['subject_tag'] . '] ', '', $subject);
		}

		$st = self::PDO()->prepare('INSERT INTO lists_archives
			(list_id, subject, `from`, content, message_id, in_reply_to, date, parent_id, topic_id, level, full_message)
				VALUES (?, ?, ?, ?, ?, ?, '.$date.', ?, ?, ?, ?);');

		$st->execute([
			$list['id'],
			$subject,
			$message->getHeader('from'),
			$message->removeSignature($message->getBody()),
			$id,
			$in_reply,
			$parent,
			$topic,
			$level,
			$message->utf8_encode($original),
		]);

		return true;
	}

	static protected function mail($to, $subject, $content, $additional_headers = [])
	{
		if (is_string($additional_headers))
		{
			$headers = $additional_headers;
		}
		else
		{
			$headers = '';

			$additional_headers['MIME-Version'] = '1.0';
			$additional_headers['Content-type'] = 'text/plain; charset=UTF-8';

			foreach ($additional_headers as $name=>$value)
			{
				$headers .= $name . ': '.$value."\r\n";
			}
		}

		$headers = preg_replace("#(?<!\r)\n#si", "\r\n", $headers);

		$content = trim($content);
		$content = preg_replace("#(?<!\r)\n#si", "\r\n", $content);

		$subject = '=?UTF-8?B?'.base64_encode($subject).'?=';

		return mail($to, $subject, trim($content), trim($headers));
	}

	public function create($name, $domain, $admin)
	{
		if (!trim($name) || !trim($domain) || !trim($admin))
		{
			throw new NG_User_Exception('Invalid name or domain.');
		}

		if (!preg_match('/^[a-z0-9_]+$/', $name))
		{
			throw new NG_User_Exception('Invalid name: must only contain alphanumeric characters and underscores.');
		}

		$a = self::PDO()->prepare('SELECT 1 FROM user WHERE username = ? AND domain = ?;');
		$a->execute([$name, $domain]);

		$b = self::PDO()->prepare('SELECT 1 FROM alias WHERE username = ? AND domain = ?;');
		$b->execute([$name, $domain]);

		$c = self::PDO()->prepare('SELECT 1 FROM lists WHERE name = ? AND domain = ?;');
		$c->execute([$name, $domain]);

		if (($a && $a->fetch()) || ($b && $b->fetch()) || ($c && $c->fetch()))
		{
			throw new NG_User_Exception('Invalid name: already used for an email alias, list or user.');
		}

		$admin = trim($admin);

		if (!filter_var($admin, FILTER_VALIDATE_EMAIL))
		{
			throw new NG_User_Exception('Invalid admin email address.');
		}

		$st = self::PDO()->prepare('INSERT INTO lists (name, domain, admin, owner) VALUES (?, ?, ?, ?);');
		$st->bindValue(1, $name);
		$st->bindValue(2, $domain);
		$st->bindValue(3, $admin);
		$st->bindValue(4, $this->uid);

		return $st->execute();
	}

	public function delete($id)
	{
		$st = self::PDO()->prepare('DELETE FROM lists_archives WHERE list_id = ?;');
		$st->execute([(int)$id]);

		$st = self::PDO()->prepare('DELETE FROM lists_members WHERE list_id = ?;');
		$st->execute([(int)$id]);

		$st = self::PDO()->prepare('DELETE FROM lists WHERE id = ?;');
		return $st->execute([(int)$id]);
	}

	public function editMember($id, $address, $moderator = false)
	{
		$address = trim($address);

		if (!filter_var($address, FILTER_VALIDATE_EMAIL))
		{
			throw new NG_User_Exception('"'.$address.'" is not a valid email address.');
		}

		$moderator = (int)(bool)$moderator;

		$st = self::PDO()->prepare('UPDATE lists_members SET address = ?, moderator = ? WHERE id = ?;');
		return $st->execute([$address, $moderator, (int)$id]);
	}

	public function removeMembers($id, $members, $send_goodbye = false)
	{
		if (!is_array($members))
		{
			throw new \UnexpectedValueException('$members is supposed to be an array');
		}

		$arr = [];

		foreach ($members as $key=>&$value)
		{
			if (!is_numeric($value))
			{
				continue;
			}

			$arr[] = (int)$value;
		}

		if (empty($arr))
		{
			return true;
		}

		if ($send_goodbye)
		{
			$list = $this->get($id);

			$st = self::PDO()->prepare('SELECT address FROM lists_members
				WHERE list_id = ? AND id IN ('.implode($arr, ', ').');');
			$st->execute([(int)$id]);

			while ($row = $st->fetch(PDO::FETCH_NUM))
			{
				self::sendGoodbye($list, $row[0]);
			}
		}

		$st = self::PDO()->prepare('DELETE FROM lists_members
			WHERE list_id = ? AND id IN ('.implode($arr, ', ').');');
		return $st->execute([(int)$id]);
	}

	public function listAll()
	{
		$st = self::PDO()->prepare('SELECT *,
			(SELECT COUNT(*) FROM lists_members WHERE list_id = lists.id) AS nb_members
			FROM lists WHERE owner = ? ORDER BY domain, name;');
		$st->bindValue(1, $this->uid);
		$st->execute();
		return $st->fetchAll();
	}

	public function resetMemberBounce($list_id, $member_id)
	{
		$st = self::PDO()->prepare('UPDATE lists_members SET stats_bounced = 0 WHERE list_id = ? AND id = ?;');
		return $st->execute([(int)$list_id, (int)$member_id]);
	}

}

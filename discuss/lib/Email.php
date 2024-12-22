<?php

namespace Paheko\Plugin\Discuss;

use Paheko\Plugin\Discuss\Entities\Forum;

class Email
{
	const MAX_MESSAGE_SIZE = 25*1024*1024;

	// Reject messages with a spam score greater than this
	const REJECT_SPAM_SCORE = 20;

	protected Forum $forum;

	public function __construct(Forum $forum)
	{
		$this->forum = $forum;
	}

	public function receive(string $message)
	{
		if ($message > self::MAX_MESSAGE_SIZE) {
			throw new EmailException(sprintf('Message is too large, max allowed size is %d MiB', self::MAX_MESSAGE_SIZE/1024/1024), 413);
		}

		$msg = new Mail_Message;
		$msg->parse($message);

		$recipients = $msg->getRecipientsAddresses();

		foreach ($recipients as $recipient) {
			$email = self::extractListAddress($recipient);

			$forum = Forums::getByAddress($email->address);

			if ($forum) {
				break;
			}
		}

		if (!$forum) {
			throw new EmailException('Unknown list address: ' . $address, 404);
		}

		$self = new self($forum);
		$self->route($email->command, $email->param, $msg);
	}

	static public function extractAddress(string $raw): \stdClass
	{
		if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
			throw new EmailException('Invalid email address: ' . $raw, 400);
		}

		$local_part = strtok($to, '@');
		$domain = strtok('');
		$name = strtok($local_part, SEPARATOR);
		$command = strtok(SEPARATOR);
		$param = strtok('');

		$address = $local_part . '@' . $domain;
		return (object) compact('local_part', 'domain', 'name', 'command', 'param', 'address');
	}

	static public function webhook(string $to, string $message)
	{
		if ($message > self::MAX_MESSAGE_SIZE) {
			throw new EmailException(sprintf('Message is too large, max allowed size is %d MiB', self::MAX_MESSAGE_SIZE/1024/1024), 413);
		}

		$msg = new Mail_Message;
		$msg->parse($message);

		$email = self::extractAddress($to);
		$forum = self::getByAddress($email->address);

		if (!$forum) {
			throw new EmailException('Unknown list address: ' . $address, 404);
		}

		$self = new self($forum);
		$self->route($command, $param, $msg);
	}

	public function route(string $command, string $param, Mail_Message $message)
	{
		$from = $message->getFromAddress();

		if ($command === 'join') {
			return $this->join($message);
		}
		elseif ($command === 'leave') {
			return $this->leave($from);
		}
		elseif ($command === 'internal') {
			return $this->post($from, $message, true);
		}
		elseif ($command === 'bounce' && ctype_digit($param)) {
			return $this->bounceUser((int)$param, $message);
		}
		elseif ($command === 'bounce' && !$param) {
			return $this->bounce($message);
		}
		elseif ($command) {
			throw new EmailException('Unknpwn command: ' . $command, 404);
		}
		else {
			return $this->post($from, $message);
		}
	}

	public function bounceUser(int $id_user, Mail_Message $message)
	{
		$user = $this->forum->getUser($id_user);

		if (!$user) {
			// User does not exist?!
			return;
		}

		$return = $message->identifyBounce();
		$email = $user->email();

		if (!$email) {
			// This user has no email?! then how can it bounce?
			return;
		}

		// Mark address as bounced
		$address = Emails::getAddress($email);
		$address->hasBounced($return['type'], $return['message']);
		$address->save();
	}

	public function bounce(Mail_Message $message)
	{
		$return = $message->identifyBounce();

		if (empty($return['recipient'])) {
			return;
		}

		// Mark address as bounced
		$address = Emails::getAddress($return['recipient']);
		$address->hasBounced($return['type'], $return['message']);
		$address->save();
	}

	public function join(string $from)
	{
		$user = $this->forum->getUserByEmail($from);

		if ($user) {
			throw new EmailException('You are already a member of this mailing list', 409);
		}

		if ($this->forum->subscribe_permission === Forum::CLOSED) {
			throw new EmailException('Subscriptions to this list are closed.', 403);
		}

		if ($this->forum->subscribe_permission === Forum::RESTRICTED) {
			throw new \Exception('TODO');
		}

		$this->forum->requestJoin($from);
	}

	public function leave(string $from)
	{
		$this->forum->requestLeave($from);
	}

	static protected function post(string $from, Mail_Message $message, bool $internal = false)
	{
		if ((float)$message->getHeader('x-spam-score') >= self::REJECT_SPAM_SCORE) {
			throw new EmailException('Your message was identified as spam.', 403);
		}

		if (stristr($message->getHeader('list-id'), $this->forum->list_id())) {
			return;
			//throw new EmailException("Detected loop on list, discarding message.", 400);
		}

		if (stristr($msg->getHeader('x-loop'), $this->forum->email())) {
			return;
			//throw new EmailException("Detected from loop on list, discarding message.", 400);
		}

		$user = $this->forum->getUserByEmail($from);

		if (!$user && $this->forum->post_permission !== Forum::OPEN) {
			throw new EmailException('Posting to this list is only allowed to members and you are not one of them.', 403);
		}

		if ($user) {
			if (!$user->is_moderator && $this->forum->post_permission === Forum::CLOSED) {
				throw new EmailException('Posting to this list is only allowed to moderators and you are not one of them.', 403);
			}

			if ($user->is_banned) {
				return;
			}
		}

		$subject = trim($message->getHeader('subject'));

		if (empty($subject)) {
			$subject = date('Message sans sujet du %s', date('d/m/Y H:i'));
		}

		// Remove Re: prefixes
		$subject = preg_replace('/^(?:Re\s*:\s*)*/', '', $subject);

		// Remove list tag from subject (if any)
		$subject = preg_replace('!\s*\[' . $this->forum->uri . '\]\s*!', '', $subject);
		$subject = trim($subject);


		$body = $message->getBody();
		$body = $message->removeSignature($body);
		$body = $message->removeTrailingQuote($body);

		$m = $this->forum->createMessage($body);
		$m->set('id_user', $user ? $user->id : null);
		$m->set('from_name', !$user ? $message->getFromName() : null);
		$m->set('from_email', !$user ? $from : null);
		$m->set('content', $body);
		$m->set('message_id', $message->getMessageId());

		$parent = null;

		if ($msgid = $message->getInReplyTo()) {
			$parent = $this->forum->getParentMessageByMessageId($msgid);
		}

		if ($parent) {
			if ($parent->thread()->is_closed) {
				throw new EmailException('This thread is closed.', 403);
			}

			$m->set('id_thread', $parent->id_thread);
			$m->set('id_parent', $parent->id);
			$m->set('level', $parent->level + 1);
		}
		else {
			$thread = $this->forum->createThread($subject);
			$thread->save();
			$m->set('id_thread', $thread->id);
		}

		$m->save();

		$root = $message->storage_root();

		$deleted_attachments = [];

		// Add attachments
		foreach ($message->getParts() as $pid => $part) {
			$delete = null;

			if ($this->forum->max_attachment_size
				&& strlen($part['content']) > $this->forum->max_attachment_size) {
				$delete = 'too_large';
			}
			// Always allow embedded text, unless too large
			elseif (empty($part['name'])
				&& strstr($part['type'], 'text/')) {
				continue;
			}
			elseif ($this->forum->delete_forbidden_attachments
				&& !empty($part['name'])
				&& ($pos = strrpos($part['name'], '.'))
				&& ($ext = substr($part['name'], $pos+1))
				&& !array_key_exists($ext, self::ALLOWED_ATTACHMENT_TYPES)) {
				$delete = $ext;
			}
			elseif ($this->forum->delete_forbidden_attachments
				&& !empty($part['content'])) {
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$type = $finfo->buffer($part['content']);
				unset($finfo);

				if (!in_array($type, self::ALLOWED_ATTACHMENT_TYPES)) {
					$delete = $type;
				}
			}

			if ($delete !== null) {
				$deleted_attachments[] = [
					'name'   => !empty($part['name']) ? $part['name'] : 'Attachment #' . $pid,
					'reason' => $delete,
					'size'   => strlen($part['content']),
				];
				continue;
			}

			$name = $part['id'];
			Files::createFromString($root . '/' . $name, $part['content']);
			$m->set('has_attachments', true);
		}

		if (count($deleted_attachments)) {
			$m->set('deleted_attachments', $deleted_attachments);
		}

		$m->save();

		$this->forwardMessageToSubscribers($m);
	}

	public function forwardMessageToSubscribers(Message $m)
	{
		$msg = new Mail_Message;
		$subject = $m->thread()->subject;

		if ($m->id_parent) {
			$subject = 'Re: ' . $subject;
		}

		$list_tag = sprintf('[%s]', $this->forum->uri);
		$subject = $list_tag . ' ' . $subject;

		$msg->setHeader('Subject', $subject);

		$body = $m->content;

		if ($this->forum->template_footer) {
			$body .= "\n\n-- \n";
			$body .= $this->forum->template_footer;
		}

		$msg->setTextBody($body);
		$msg->setHTMLBody($m->html($body));

		$unsubscribe_url = $this->forum->unsubscribe_url();
		$list_address = $this->forum->email();
		$list_id = $this->forum->list_id();

		$msg->appendHeaders([
			'subject' => $subject,
			'sender' => $list_address,
			'from' => sprintf('"%s" <%s>', str_replace('"', '', mb_substr($m->name() ?? 'Anonyme', 0, 100)), $list_address),
			'list-id' => '<' . $list_address . '>',
			'list-unsubscribe' => sprintf('<%s>, <mailto:%s>', $unsubscribe_url, $this->forum->email('leave')),
			'list-post' => sprintf('<mailto:%s>', $list_address),
			//'list-help' => sprintf('<mailto:%s-help@%s>', $list['name'], $list['domain']),
			'X-Loop' => $list_address,
			'Precedence' => 'list',
		]);

		$msg->setMessageId();

		if ($parent = $m->parent()) {
			$msg->setHeader('In-Reply-To', sprintf('<%s>', $parent->message_id));
		}

		// If message is posted by a non-subscriber, add their address to the reply-to
		if ($this->forum->post_permission === Forum::OPEN && !$m->user()) {
			$msg->setHeader('Reply-To', sprintf('%s, %s', $list_address, $m->from_email));
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

}

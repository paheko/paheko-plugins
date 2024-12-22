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
		'bundle' => '', // Fossil bundle
	];

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

	protected function createToken(?string $random = null, ?int $expiry = null)
	{
		$random ??= bin2hex(random_bytes(4));
		$expiry ??= ceil(time() / 1800) + 1;
		return hash_hmac('md5', $this->id() . $time, SECRET_KEY) . ':' . $expiry . ':' . $random;
	}

	protected function verifyToken(string $token): bool
	{
		$user_hash = strtok('user_hash', ':');
		$expiry = strtok(':');
		$random = strtok('');
		$now = ceil(time() / 1800);

		if ($now < $expiry) {
			return false;
		}

		return $user_hash === $this->createToken($random, $expiry);
	}

	public function requestJoin(string $address)
	{
		$token = $this->createToken();

		$subject = sprintf('Confirmez votre inscription à "%s"', $this->title);
		$url = $this->url() . '?' . http_build_query(['j' => $address, 't' => $token]);
		$text = sprintf("Pour confirmer votre inscription, cliquez sur l'adresse suivante :\n\n%s", $url);
		$this->sendTo($address, $subject, $text);
	}

	public function verifyJoinRequest(string $address, string $token)
	{
		if (!$this->verifyToken($token)) {
			throw new UserException('Requête invalide. Merci de bien vouloir recommencer.', 400);
		}

		$user = $this->createUser();
		$user->email = $address;
		$user->save();

		$this->sendWelcome($user);

		return $user;
	}

	protected function sendToUser(User $user, string $suject, string $text)
	{
		$email = $user->email();

		if (!$email) {
			return;
		}

		$this->sendTo($email, $subject, $text);
	}

	protected function sendTo(string $email, string $subject, $text)
	{
		Emails::queue(Emails::CONTEXT_SYSTEM, [$email], $this->email('request'), $subject, $text);
	}

	protected function sendWelcome(User $user)
	{
		$subject = sprintf('%s — Bienvenue !', $this->title);

		$text = $this->template_welcome;
		$text ??= sprintf("Bienvenue sur ce forum !\n\nCliquez ici pour vous rendre sur le forum :\n\n%s", $this->url());

		$this->sendToUser($user->email(), $subject, $text);
	}

	public function sendLoginLink(string $address)
	{
		// FIXME
	}

	public function requestLeave(string $address)
	{
		$user = $this->forum->getUserByEmail($address);

		if (!$user) {
			throw new UserException('Cette adresse n\'est pas inscrite à ce forum', 404);
		}

		$token = $this->createToken();

		$subject = sprintf('Confirmez votre désinscription de "%s"', $this->title);
		$url = $this->url() . '?' . http_build_query(['l' => $address, 't' => $token]);
		$text = sprintf("Pour confirmer votre désinscription, cliquez sur l'adresse suivante :\n\n%s", $url);
		$this->sendTo($address, $subject, $text);
	}

	public function verifyLeaveRequest(string $address, string $token)
	{
		if (!$this->verifyToken($token)) {
			throw new UserException('Requête invalide. Merci de bien vouloir recommencer.', 400);
		}

		$user = $this->forum->getUserByEmail($address);

		if (!$user) {
			throw new UserException('Cette adresse n\'est pas inscrite à ce forum', 404);
		}

		$this->leave($user);
	}

	public function leave(User $user)
	{
		$this->sendGoodbye($user);
		$user->delete();
	}

	public function list_id(): string
	{
		return str_replace('@', '.', $this->email());
	}
}

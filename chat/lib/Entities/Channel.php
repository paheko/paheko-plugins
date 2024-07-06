<?php

namespace Paheko\Plugin\Chat\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Files\Files;
use Paheko\Entities\Files\File;

use KD2\DB\EntityManager as EM;

class Channel extends Entity
{
	const TABLE = 'plugin_chat_channels';

	protected ?int $id;
	protected ?string $name = null;
	protected ?string $description = null;
	protected string $access;
	protected int $archived = 0;
	protected ?int $delete_after = null;
	protected ?int $max_history = null;

	const ACCESS_PUBLIC = 'public'; // everyone
	const ACCESS_PRIVATE = 'private'; // logged-in users only
	const ACCESS_INVITE = 'invite'; // invited users/invited anonymous only
	const ACCESS_DIRECT = 'direct'; // direct messages

	const ACCESS_LIST = [
		self::ACCESS_PUBLIC => 'Discussion publique',
		self::ACCESS_PRIVATE => 'Discussion privée, réservée aux membres connectés',
		self::ACCESS_INVITE => 'Discussion privée, sur invitation',
		self::ACCESS_DIRECT => 'Discussion privée, entre deux personnes',
	];

	const DELETE_AFTER_OPTIONS = [
		1800          => '30 minutes',
		3600          => '1 heure',
		3600*12       => '12 heures',
		3600*24       => '24 heures',
		3600*24*7     => '7 jours',
		3600*24*15    => '15 jours',
		3600*24*30    => '30 jours',
		3600*24*60    => '2 mois',
		3600*24*90    => '3 mois',
		3600*24*180   => '6 mois',
		3600*24*365   => '1 an',
		3600*24*365*2 => '2 ans',
		3600*24*365*3 => '3 ans',
	];

	public function getAccessLabel(): string
	{
		return self::ACCESS_LIST[$this->access];
	}

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(in_array($this->access, [self::ACCESS_PUBLIC, self::ACCESS_INVITE, self::ACCESS_PRIVATE, self::ACCESS_DIRECT]));

		if ($this->access === self::ACCESS_DIRECT) {
			$this->assert(!isset($this->name));
			$this->assert(!isset($this->description));
		}
		else {
			$this->assert(trim($this->name) !== '', 'Le nom ne peut rester vide.');
			$this->assert(mb_strlen($this->name) <= 49, 'Le nom doit faire moins de 49 caractères.');
			$this->assert(!preg_match('/^[a-zA-Z0-9\p{L}_-]+$/U', $this->name), 'Le nom contient des caractères invalides.');
			$this->assert(!isset($this->description) || strlen($this->description) < 65000, 'La description doit faire moins de 65.000 caractères.');
		}

		$this->assert(null === $this->delete_after || array_key_exists($this->delete_after, self::DELETE_AFTER_OPTIONS));
		$this->assert(null === $this->max_history || $this->max_history > 0, 'Le nombre de messages conservés ne peut être zéro ou négatif.');
	}

	public function importForm(array $source = null)
	{
		$source ??= $_POST;

		if (array_key_exists('delete_after', $source) && empty($source['delete_after'])) {
			$source['delete_after'] = null;
		}

		if (array_key_exists('max_history', $source) && empty($source['max_history'])) {
			$source['max_history'] = null;
		}

		parent::importForm($source);
	}

	public function prune(): void
	{
		$db = DB::getInstance();
		$em = EM::getInstance(Message::class);
		$db->begin();

		if ($this->delete_after) {
			$time = time() - $this->delete_after;
			$db->preparedQuery('DELETE FROM plugin_chat_messages WHERE id_channel = ? AND id_file IS NULL AND added < ?;', $this->id(), $time);

			$i = $em->iterate('SELECT * FROM @TABLE WHERE id_channel = ? AND added < ? AND id_file IS NOT NULL;', $this->id(), $time);
			foreach ($i as $row) {
				$row->delete();
			}
		}

		if ($this->max_history) {
			$last_id = $db->firstColumn('SELECT id FROM plugin_chat_messages WHERE id_channel = ? ORDER BY id DESC ?,1;', $this->id(), $this->max_history);

			if ($last_id) {
				$db->preparedQuery('DELETE FROM plugin_chat_messages WHERE id_channel = ? AND id_file IS NULL AND id < ?);', $this->id(), $last_id);

				$i = $em->iterate('SELECT * FROM @TABLE WHERE id_channel = ? AND id_file IS NOT NULL AND id < ?;', $this->id(), $time, $last_id);
				foreach ($i as $row) {
					$row->delete();
				}
			}
		}

		$db->commit();
	}

	public function delete(): bool
	{
		// Delete all files
		if ($dir = Files::get($this->storage_root())) {
			$dir->delete();
		}

		return parent::delete();
	}


	public function join(User $user): ?int
	{
		$db = DB::getInstance();

		$last_seen_message_id = $db->firstColumn('SELECT last_seen_message_id FROM plugin_chat_users_channels WHERE id_channel = ? AND id_user = ?;', $this->id(), $user->id());

		$db->begin();

		if ($last_seen_message_id !== false) {
			$db->preparedQuery('UPDATE plugin_chat_users_channels SET last_connect = ? WHERE id_channel = ? AND id_user = ?;',
				time(), $this->id(), $user->id());
		}
		else {
			$db->insert('plugin_chat_users_channels', [
				'last_connect' => time(),
				'id_channel'   => $this->id(),
				'id_user'      => $user->id(),
			]);
			$last_seen_message_id = null;
		}

		$user->set('last_connect', time());
		$user->set('last_disconnect', null);
		$user->save();
		$db->commit();

		return $last_seen_message_id;
	}

	public function getRecipient(User $me): ?User
	{
		if ($this->access !== self::ACCESS_DIRECT) {
			return null;
		}

		return EM::findOne(User::class, 'SELECT u.*
			FROM @TABLE u
			INNER JOIN plugin_chat_users_channels c ON c.id_user = u.id
			WHERE c.id_channel = ? ORDER BY u.id != ? LIMIT 1', $this->id(), $me->id());
	}

	public function addUser(User $user): void
	{
		DB::getInstance()->insert('plugin_chat_users_channels', ['id_user' => $user->id, 'id_channel' => $this->id()]);
	}

	public function say(User $user, string $text): Message
	{
		$now = time();

		$message = new Message;
		$message->import([
			'id_channel'   => $this->id(),
			'id_thread'    => null,
			'added'        => $now,
			'id_user'      => $user->id,
			'user_name'    => $user->id_user ? null : $user->name,
			'type'         => Message::TYPE_TEXT,
			'content'      => trim($text),
			'reactions'    => null,
			'last_updated' => $now,
		]);

		$message->save();
		return $message;
	}

	public function reactTo(User $user, int $message_id, string $emoji): ?Message
	{
		$message = EM::findOne(Message::class, 'SELECT * FROM @TABLE WHERE id = ? AND id_channel = ?;', $message_id, $this->id());

		if (!$message) {
			return $message;
		}

		$message->react($user, $emoji);
		return $message;
	}

	public function storage_root(): string
	{
		return File::CONTEXT_EXTENSIONS . '/p/chat/' . $this->id();
	}

	public function uploadRecording(User $user, string $key): Message
	{
		$name = sprintf('recording_%s_%s.opus', date('Y-m-d.His'), bin2hex(random_bytes(4)));
		return self::uploadFile($user, $key, $name);
	}

	public function uploadFile(User $user, string $key, ?string $name = null): Message
	{
		$file = Files::upload($this->storage_root(), $key, $name);

		try {
			$now = time();

			$message = new Message;
			$message->import([
				'id_channel'   => $this->id(),
				'id_thread'    => null,
				'added'        => $now,
				'id_user'      => $user->id,
				'user_name'    => $user->id_user ? null : $user->name,
				'type'         => Message::TYPE_FILE,
				'id_file'      => $file->id(),
				'reactions'    => null,
				'last_updated' => $now,
			]);

			$message->save();
			return $message;
		}
		catch (\Throwable $e) {
			$file->delete();
			throw $e;
		}
	}

	public function requiresLogin(): bool
	{
		return $this->access !== self::ACCESS_PUBLIC;
	}

	public function listUsers(): array
	{
		$sql = 'SELECT u.* FROM @TABLE u
			INNER JOIN plugin_chat_users_channels uc ON uc.id_user = u.id
			WHERE uc.id_channel = ? AND (u.id_user IS NOT NULL OR last_disconnect < ?)
			ORDER BY u.name COLLATE U_NOCASE;';
		return EM::getInstance(User::class)->all($sql, $this->id(), time() - 3600);
	}

	public function listMessages(?int $focus = null, ?int $count = 100): array
	{
		$query = 'SELECT m.*,
			CASE WHEN u.id IS NOT NULL THEN u.name ELSE m.user_name END AS user_name,
			CASE WHEN u.id_user IS NOT NULL THEN u.id_user ELSE NULL END AS real_user_id
			FROM plugin_chat_messages m
			LEFT JOIN plugin_chat_users u ON u.id = m.id_user
			WHERE m.id_channel = %d %s
			ORDER BY id %s
			LIMIT %d';

		if ($focus) {
			$count = round($count / 2);
			$sql = sprintf('SELECT * FROM (%s) UNION ALL SELECT * FROM (%s) ORDER BY id ASC;',
				sprintf($query, $this->id(), ' AND m.id <= ' . $focus, 'DESC', $count),
				sprintf($query, $this->id(), ' AND m.id > ' . $focus, 'ASC', $count)
			);
		}
		else {
			$sql = sprintf($query, $this->id(), '', 'ASC', $count);
		}

		return DB::getInstance()->get($sql);
	}

	public function getEventsSince(int $since, int $last_seen_message_id, User $user): \Generator
	{
		$db = DB::getInstance();

		$sql = 'SELECT m.*, CASE WHEN u.id IS NOT NULL THEN u.name ELSE m.user_name END AS user_name,
			CASE WHEN u.id_user IS NOT NULL THEN u.id_user ELSE NULL END AS real_user_id,
			m2.*
			FROM plugin_chat_messages m
			INNER JOIN (SELECT id, LAG(added) OVER (ORDER BY id) AS previous_added, LAG(id_user) OVER (ORDER BY id) AS previous_user_id FROM plugin_chat_messages) AS m2 ON m2.id = m.id
			LEFT JOIN plugin_chat_users u ON u.id = m.id_user
			WHERE m.id_channel = ? AND (m.id > ? OR (last_updated != added AND last_updated > ?)) ORDER BY id;';

		foreach ($db->iterate($sql, $this->id(), $last_seen_message_id, $since) as $message) {
			yield [
				'type' => 'message',
				'data' => [
					'message' => $message,
				]
			];
		}
	}
}

<?php

namespace Paheko\Plugin\Chat\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;
use Paheko\UserException;
use Paheko\Utils;

class Message extends Entity
{
	const TABLE = 'plugin_chat_messages';

	protected ?int $id;
	protected int $id_channel;
	protected ?int $id_thread = null;
	protected int $added;
	protected ?int $id_user = null;
	protected ?string $user_name = null;
	protected string $type;
	protected ?int $id_file = null;
	protected ?string $content = null;
	protected ?array $reactions;
	protected int $last_updated;

	const TYPE_TEXT = 'text';
	const TYPE_FILE = 'file';
	const TYPE_COMMENT = 'comment'; // /me command

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(in_array($this->type, [self::TYPE_TEXT, self::TYPE_FILE, self::TYPE_COMMENT]));

		if (null !== $this->id_user) {
			$this->assert(null === $this->user_name);
		}
		elseif ($this->exists()) {
			// Cannot add message with no ID user / no user name
			$this->assert(null !== $this->user_name);
		}

		if ($this->type === self::TYPE_FILE) {
			$this->assert(null === $this->content);
		}
		else {
			$this->assert(null !== $this->content && trim($this->content) !== '', 'Le texte ne peut rester vide.');
			$this->assert(strlen($this->content) < 20000, 'Le texte doit faire moins de 20.000 caractères.');
		}
	}

	public function edit(string $content)
	{
		if ($this->content !== 'text') {
			throw new \LogicException('Cannot edit a non-text message');
		}

		$this->set('content', trim($content));
		$this->set('last_updated', time());
		$this->save();
	}

	public function delete(): bool
	{
		$this->set('content', null);
		$this->set('type', null);
		$this->set('reactions', null);
		$this->set('last_updated', time());
		$this->save();
		return true;
	}

	public function react(string $emoticon)
	{
		if (\IntlChar::charType($emoticon) === null) {
			throw new \InvalidArgumentException('Invalid emoticon character');
		}

		$reactions = $this->reactions;

		$reactions[$emoticon] ??= 0;
		$reactions[$emoticon]++;

		$this->set('reactions', $reactions);
		$this->set('last_updated', time());
		$this->save();
	}
}

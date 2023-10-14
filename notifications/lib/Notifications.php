<?php

namespace Paheko\Plugin\Notifications;

use Paheko\Entities\Files\File;
use Paheko\Entities\Email\Email;
use Paheko\Entities\Plugin;
use Paheko\Entities\Signal;

use Paheko\Users\Session;
use Paheko\Email\Emails;

use Paheko\Utils;
use Paheko\ValidationException;

use KD2\Mail_Message;
use KD2\SimpleDiff;

class Notifications
{
	const SIGNALS = [
		'web.page.version.new'           => 'Page du site web modifiée',
		'reminder.send.after'            => 'Rappel automatique envoyé à un membre',
		'file.create'                    => 'Fichier créé',
		'file.overwrite'                 => 'Fichier modifié',
		'file.trash'                     => 'Fichier mis à la corbeille',
		'file.delete'                    => 'Fichier supprimé',
		'entity.Users\User.create.after' => 'Membre ajouté',
		'entity.Users\User.modify.after' => 'Membre modifié',
		'entity.Users\User.delete.after' => 'Membre supprimé',
	];

	const FILE_CONTEXTS = [
		'all' => 'Tous',
		File::CONTEXT_DOCUMENTS => 'Documents',
		File::CONTEXT_TRANSACTION => 'Écritures comptables',
		File::CONTEXT_USER => 'Membres',
		File::CONTEXT_CONFIG => 'Configuration',
		File::CONTEXT_WEB => 'Pages du site web',
		File::CONTEXT_MODULES => 'Code des modules',
	];

	const ACTIONS = [
		'email' => 'Envoyer un e-mail',
	];

	protected Plugin $plugin;
	protected array $notifications;

	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
		$this->notifications = (array) $plugin->getConfig('notifications');
	}

	public function add(string $signal, string $action, array $config)
	{
		if (!array_key_exists($signal, self::SIGNALS)) {
			throw new ValidationException('Invalid signal: ' . $signal);
		}

		if (!array_key_exists($action, self::ACTIONS)) {
			throw new ValidationException('Invalid action: ' . $action);
		}

		$config = (object) $config;

		if (empty($config->email)) {
			throw new ValidationException('Invalid email address');
		}

		Email::validateAddress($config->email);

		if (0 === strpos($signal, 'file.')) {
			if (empty($config->file_context) || !array_key_exists($config->file_context, self::FILE_CONTEXTS)) {
				throw new ValidationException('Invalid file context');
			}
		}
		else {
			unset($config->file_context);
		}

		$this->notifications[] = (object) compact('signal', 'action', 'config');
		$this->plugin->registerSignal($signal, self::class . '::signal');
	}

	public function remove(int $idx): void
	{
		$this->plugin->unregisterSignal($this->notifications[$idx]->signal);
		unset($this->notifications[$idx]);
	}

	public function save(): void
	{
		$this->plugin->setConfigProperty('notifications', $this->notifications);
		$this->plugin->save();
	}

	public function list(): array
	{
		$out = [];

		foreach ($this->notifications as $n) {
			$n->signal_label = self::SIGNALS[$n->signal];
			$n->action_label = self::ACTIONS[$n->action];
			$n->file_context = isset($n->config->context) ? self::FILE_CONTEXTS[$n->config->context] : null;
		}

		return $this->notifications;
	}

	static public function signal(Signal $signal, Plugin $plugin): void
	{
		static $self = null;

		if (null === $self) {
			$self = new self($plugin);
		}

		$self->notify($signal);
	}

	public function notify(Signal $signal)
	{
		$name = $signal->getName();
		$emails = [];

		foreach ($this->notifications as $n) {
			if ($n->signal !== $name) {
				continue;
			}

			if (0 === strpos($name, 'file.')) {
				// Skip if context does not match
				if ($n->config->file_context !== 'all' && $n->config->file_context !== $signal->getIn('file')->context()) {
					continue;
				}
			}

			$emails[] = $n->config->email;
		}

		if (!count($emails)) {
			return;
		}

		Emails::queue(Emails::CONTEXT_NOTIFICATION, $emails, null, '[Notif.] ' . $this->getSubject($signal), $this->getBody($signal));
	}

	public function getSubject(Signal $signal): string
	{
		$name = $signal->getName();

		switch ($name) {
			case 'web.page.version.new':
				return sprintf('La page "%s" a été modifiée', $signal->getIn('entity')->title);
			case 'reminder.send.after':
				$r = $signal->getIn('reminder');
				return sprintf('Rappel envoyé à %s (%s)', $r->identity, $r->label);
			case 'file.create':
			case 'file.overwrite':
			case 'file.trash':
			case 'file.delete':
				return sprintf('%s : %s', self::SIGNALS[$name], $signal->getIn('file')->path);
			case 'entity.Users\User.create.after':
			case 'entity.Users\User.modify.after':
			case 'entity.Users\User.delete.after':
				return sprintf('%s : %s', self::SIGNALS[$name], $signal->getIn('entity')->name());
		}

		throw new \LogicException('Unknown signal: ' . $signal);
	}

	public function getBody(Signal $signal): string
	{
		$name = $signal->getName();

		switch ($name) {
			case 'web.page.version.new':
				$page = $signal->getIn('entity');
				$version = $signal->getIn('version');

				$out = sprintf("%s\nModifications : %s\n\n", $page->url(), Utils::getLocalUrl(sprintf('!web/?id=%d&history=%d', $page->id, $version->id)));
				$out .= SimpleDiff::diff($signal->getIn('old_content'), $signal->getIn('content'));

				return $out;
			case 'reminder.send.after':
				$r = $signal->getIn('reminder');

				return sprintf("Membre : %s\nActivité : %s\nExpiration : %s\n\n-------- MESSAGE ENVOYÉ --------\n\n%s",
					$r->identity,
					$r->label,
					Utils::date_fr($r->expiry_date, 'd/m/Y'),
					$r->body
				);
			case 'file.create':
			case 'file.overwrite':
				return $signal->getIn('file')->url();
			case 'file.trash':
				return 'Fichier déplacé à la corbeille. Il sera supprimé dans 30 jours automatiquement.';
			case 'file.delete':
				return 'Fichier supprimé définitivement.';
			case 'entity.Users\User.modify.after':
				$user = $signal->getIn('entity');
				$out = "Fiche du membre : ". $user->url() . "\n\n";
				$a = $user->asDetailsArray();
				$b = $signal->getIn('modified_properties');

				$b = array_map(fn($v) => is_object($v) && $v instanceof \DateTimeInterface ? $v->format('d/m/Y') : $v, $b);

				foreach ($b as $key => $value) {
					if (!array_key_exists($key, $a)) {
						continue;
					}

					$out .= sprintf("%s :\n- %s\n+ %s\n\n\n", $key, $b[$key], $a[$key]);
				}

				return $out;
			case 'entity.Users\User.create.after':
			case 'entity.Users\User.delete.after':
				$user = $signal->getIn('entity');
				$out = "Fiche du membre : ". $user->url() . "\n\n";

				foreach ($user->asDetailsArray() as $key => $value) {
					$out .= sprintf("%s :\n%s\n\n", $key, $value);
				}

				return $out;
		}

		throw new \LogicException('Unknown signal: ' . $signal);
	}
}

<?php

namespace Paheko\Plugin\Webmail\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\Plugins;
use Paheko\Security;
use Paheko\UserException;
use Paheko\Entities\Email\Email;

use KD2\SMTP;
use KD2\SMTP_Exception;

class Account extends Entity
{
	const TABLE = 'plugin_webmail_accounts';

	protected ?int $id;
	protected int $id_user;
	protected string $address;
	protected ?string $login = null;
	protected string $password;

	protected string $imap_server;
	protected int $imap_port;
	protected string $imap_security;

	protected string $smtp_server;
	protected int $smtp_port;
	protected string $smtp_security;

	protected ?SMTP $_smtp = null;
	protected ?string $_password = null;

	const SECURITY_OPTIONS = [
		'tls' => 'TLS (Défaut)',
	];

	const DEFAULT_IMAP_PORT = 993;
	const DEFAULT_SMTP_PORT = 587;

	public function selfCheck(): void
	{
		$this->assert(array_key_exists($this->imap_security, self::SECURITY_OPTIONS));
		$this->assert(array_key_exists($this->smtp_security, self::SECURITY_OPTIONS));

		$this->assert(isset($this->address) && strlen($this->address) > 3, 'Adresse e-mail vide ou invalide');

		Email::validateAddress($this->address, true);

		$login = $this->login ?? $this->address;
		$login_modified = $this->isModified('login') || $this->isModified('address');

		if ($login_modified || $this->isModified('smtp_server') || $this->isModified('smtp_port') || $this->isModified('smtp_security')) {
			$smtp = null;

			try {
				$smtp = $this->smtp(true);
				$smtp->connect();
				$smtp->authenticate();
			}
			catch (SMTP_Exception $e) {
				throw new UserException('Erreur de connexion au serveur SMTP : ' . $e->getMessage(), $e->getCode(), $e);
			}
			finally {
				if (isset($smtp)) {
					$smtp->disconnect();
				}
			}
		}
/*
		if ($login_modified || $this->isModified('imap_server') || $this->isModified('imap_port') || $this->isModified('imap_security')) {
			try {
				$this->mailbox()->listFolders();
			}
			catch (Mailbox_Exception $e) {
				throw new UserException('Erreur de connexion au serveur IMAP : ' . $e->getMessage(), $e->getCode(), $e);
			}
		}*/

		if ($this->exists()) {
			$this->assert(!$this->isModified('id_user'));
		}

		parent::selfCheck();
	}

	public function smtp(bool $force_reconnect = false): SMTP
	{
		if ($force_reconnect && isset($this->_smtp)) {
			$this->_smtp->disconnect();
			$this->_smtp = null;
		}

		if (!isset($this->_smtp)) {
			$this->_smtp = new SMTP($this->smtp_server, $this->smtp_port, $this->login ?? $this->address, $this->getPassword(), $this->smtp_security);
			$this->_smtp->setTimeout(10);
		}

		return $this->_smtp;
	}

	public function getPassword(): ?string
	{
		if ($this->isModified('password')) {
			$this->_password = null;
		}

		$this->_password ??= Security::decryptWithPassword(null, $this->password);
		return $this->_password;
	}

	public function setPassword(string $password)
	{
		$this->_password = $password;
		$this->set('password', Security::encryptWithPassword(null, $password));
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['id_user']) && is_array($source['id_user'])) {
			$source['id_user'] = key($source['id_user']);
		}

		parent::importForm($source);
	}
}

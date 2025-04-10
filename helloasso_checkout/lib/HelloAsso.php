<?php

namespace Paheko\Plugin\HelloAsso_Checkout;

use Paheko\Entities\Plugin;
use KD2\DB\EntityManager;

class HelloAsso
{
	const NAME = 'helloasso_checkout';
	const PROVIDER_LABEL = 'HelloAsso Checkout';
	const ACCOUNTING_DISABLED = 0;
	const ACCOUNTING_ENABLED = 1;
	const ACCOUNTING_OPTIONS = [self::ACCOUNTING_DISABLED => 'Désactivée', self::ACCOUNTING_ENABLED => 'Activée'];
	const LOG_FILE = __DIR__ . '/../logs';

	protected $plugin;
	protected ?\stdClass $config;

	static protected $_instance;

	static public function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	protected function __construct()
	{
		$this->plugin = EntityManager::getInstance(Plugin::class)->one('SELECT * FROM @TABLE WHERE name = ? LIMIT 1;', self::NAME);

		$this->config = $this->plugin->getConfig();
	}

	public function plugin(): Plugin
	{
		return $this->plugin;
	}

	public function getClientId(): ?string
	{
		return $this->config->client_id;
	}

	public function saveClient(string $client_id, string $client_secret): void
	{
		$client_id = trim($client_id);

		$api = API::getInstance();
		$api->register($client_id, $client_secret);
	}

	public function isConfigured(): bool
	{
		return empty($this->config->oauth) ? false : true;
	}

	public function getConfig(): ?\stdClass
	{
		return $this->config;
	}

	static public function log(string $message): void
	{
		file_put_contents(self::LOG_FILE, $message, FILE_APPEND);
	}
}
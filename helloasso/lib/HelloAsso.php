<?php

namespace Paheko\Plugin\HelloAsso;

use Paheko\Config;
use Paheko\DB;
use Paheko\Plugins;
use Paheko\Entities\Plugin;

use Paheko\Plugin\HelloAsso\Entities\Form;

class HelloAsso
{
	const PER_PAGE = 100;

	const MERGE_NAMES_FIRST_LAST = 0;
	const MERGE_NAMES_LAST_FIRST = 1;

	const MERGE_NAMES_OPTIONS = [
		self::MERGE_NAMES_FIRST_LAST => 'Prénom Nom',
		self::MERGE_NAMES_LAST_FIRST => 'Nom Prénom',
	];

	const PAYER_FIELDS = [
		'firstName'   => 'Prénom',
		'lastName'    => 'Nom',
		'email'       => 'Courriel',
		'address'     => 'Adresse postale',
		'city'        => 'Ville',
		'zipCode'     => 'Code postale',
		'country'     => 'Pays',
		'dateOfBirth' => 'Date de naissance',
		'company'     => 'Organisme'
	];

	const PAYER_FIELD_DEFAULT_MATCHES = [
		'address' => 'adresse',
		'city'    => 'ville',
		'zipcode' => 'code_postal'
	];

	const FIXED_PRICE_CATEGORY = 'Fixed';
	const PAY_WHAT_YOU_WANT_PRICE_CATEGORY = 'Pwyw';
	const FREE_PRICE_CATEGORY = 'Free';

	protected ?Plugin $plugin = null;
	protected ?\stdClass $config = null;

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
		$this->plugin = Plugins::get('helloasso');
		$this->config = $this->plugin->getConfig();
	}

	public function getConfig(): \stdClass
	{
		return $this->config;
	}

	public function plugin(): Plugin
	{
		return $this->plugin;
	}

	public function getLastSync(): ?\DateTime
	{
		$date = $this->plugin->getConfig('last_sync') ?? null;

		if ($date) {
			$date = new \DateTime($date);
		}

		return $date;
	}

	public function sync(): void
	{
		Forms::sync();
		$organizations = array_keys(Forms::listOrganizations());

		foreach ($organizations as $org_slug) {
			Orders::sync($org_slug);
			Payments::sync($org_slug);
			Items::sync($org_slug);
		}

		$this->plugin->setConfigProperty('last_sync', (new \DateTime)->format(\DATE_ISO8601));
		$this->plugin->save();
	}

	public function getClientId(): ?string
	{
		return $this->config->client_id ?? null;
	}

	public function saveClient(string $client_id, string $client_secret): void
	{
		$client_id = trim($client_id);

		if (isset($this->config->client_id)
			&& $client_id !== $this->config->client_id) {
			// Clear everything!
			$this->reset();
		}

		$api = API::getInstance();
		$api->register($client_id, $client_secret);
	}

	public function reset(): void
	{
		$sql = sprintf('DELETE FROM %s;', Form::TABLE);
		DB::getInstance()->exec($sql);
	}

	public function saveConfig(array $map, $merge_names, $match_email_field): void
	{
		$this->plugin->setConfigProperty('merge_names', (int) $merge_names);
		$this->plugin->setConfigProperty('match_email_field', (bool) $match_email_field);
		$this->plugin->setConfigProperty('map_user_fields', $map);
		$this->plugin->save();
	}

	public function isConfigured(): bool
	{
		return empty($this->config->oauth) ? false : true;
	}

/*
	public function listTargets(): array
	{
		return EM::getInstance(Target::class, 'SELECT * FROM @TABLE ORDER BY label;');
	}


	public function findUserForPayment(\stdClass $payer)
	{
		$map = $this->config->map_user_fields;
		$where = '';
		$params = [];

		if ($this->config->match_email_field) {
			$where = sprintf('%s = ? COLLATE NOCASE', $map->email);
			$params[] = $payer->email;
		}
		else {
			// In case we merge first and last names
			if ($map->firstName == $map->lastName) {
				$where = sprintf('%s = ? COLLATE NOCASE', $map->firstName);

				if ($this->config->merge_names == self::MERGE_NAMES_FIRST_LAST) {
					$params[] = $payer->firstName . ' ' . $payer->lastName;
				}
				else {
					$params[] = $payer->lastName . ' ' . $payer->firstName;
				}
			}
			else {
				$where = sprintf('%s = ? AND %s = ?', $map->firstName, $map->lastName);
				$params[] = $payer->firstName;
				$params[] = $payer->lastName;
			}
		}

		$user_identity = Config::getInstance()->get('champ_identite');

		$sql = sprintf('SELECT id, %s AS identity FROM membres WHERE %s;', $user_identity, $where);

		return DB::getInstance()->first($sql, ...$params);
	}

	public function getMappedUser(\stdClass $payer): array
	{
		$out = [];
		$map = $this->config->map_user_fields;

		foreach ($map as $key => $target) {
			if (!$target) {
				continue;
			}

			if (!isset($payer->$key)) {
				continue;
			}

			$value = $payer->$key;

			if ($key == 'country') {
				$value = substr($value, 0, 2);
			}

			$out[$target] = $value;
		}

		if ($map->firstName && $map->firstName == $map->lastName) {
			if ($this->config->merge_names == self::MERGE_NAMES_FIRST_LAST) {
				$out[$map->firstName] = $payer->firstName . ' ' . $payer->lastName;
			}
			else {
				$out[$map->firstName] = $payer->lastName . ' ' . $payer->firstName;
			}
		}

		return $out;
	}
*/


	static public function getPageSize(): int
	{
		return self::PER_PAGE;
	}

}

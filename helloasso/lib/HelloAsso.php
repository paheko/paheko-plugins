<?php

namespace Paheko\Plugin\HelloAsso;

use Paheko\Config;
use Paheko\DB;
use Paheko\Plugins;
use Paheko\Entities\Plugin;
use Paheko\Users\DynamicFields;

use Paheko\Plugin\HelloAsso\Entities\Form;

use DateTime;
use stdClass;

class HelloAsso
{
	const PER_PAGE = 100;

	const MERGE_NAMES_FIRST_LAST = 0;
	const MERGE_NAMES_LAST_FIRST = 1;

	const MERGE_NAMES_ORDER_OPTIONS = [
		self::MERGE_NAMES_FIRST_LAST => 'Prénom Nom',
		self::MERGE_NAMES_LAST_FIRST => 'Nom Prénom',
	];

	const PAYER_FIELDS = [
		'firstName'   => 'Prénom',
		'lastName'    => 'Nom',
		'email'       => 'Courriel',
		'address'     => 'Adresse postale',
		'city'        => 'Ville',
		'zipCode'     => 'Code postal',
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
	protected ?stdClass $config = null;

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

	public function getConfig(): ?stdClass
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

	public function saveClient(string $client_id, string $client_secret, bool $sandbox = false): void
	{
		$client_id = trim($client_id);
		$this->plugin->setConfigProperty('sandbox', $sandbox);

		if (isset($this->config->client_id)
			&& $client_id !== $this->config->client_id) {
			// Clear everything!
			$this->reset();
		}

		$api = API::getInstance();
		$api->register($client_id, $client_secret);

		$this->plugin->save();
	}

	public function reset(): void
	{
		$sql = sprintf('DELETE FROM %s;', Form::TABLE);
		DB::getInstance()->exec($sql);
	}

	public function saveConfig(array $fields_map, int $merge_names_order, bool $match_email_field): void
	{
		$this->plugin->setConfigProperty('fields_map', $fields_map);
		$this->plugin->setConfigProperty('merge_names_order', $merge_names_order);
		$this->plugin->setConfigProperty('match_email_field', $match_email_field);
		$this->plugin->save();
	}

	public function isConfigured(): bool
	{
		return empty($this->config->oauth) ? false : true;
	}

	public function findUserForPayment(stdClass $payer)
	{
		$map = $this->config->fields_map ?? new stdClass;
		$where = '';
		$params = [];
		$email_field = DynamicFields::getFirstEmailField();
		$identity_field = DynamicFields::getNameFieldsSQL();
		$db = DB::getInstance();
		$df = DynamicFields::getInstance();

		if ($this->config->match_email_field ?? null) {
			$where = sprintf('%s = ? COLLATE NOCASE', $db->quoteIdentifier($email_field));
			$params[] = $payer->email;
		}
		else {
			$order = $this->config->merge_names_order ?? self::MERGE_NAMES_FIRST_LAST;

			// Make sure the mapped field exists in the fields list
			if (!isset($map->firstName, $map->lastName)
				|| !$df->get($map->firstName)
				|| !$df->get($map->lastName)) {
				$map->firstName = $map->lastName = DynamicFields::getFirstNameField();
			}

			// In case we merge first and last names in the same field
			if ($map->firstName === $map->lastName) {
				$where = sprintf('%s = ? COLLATE U_NOCASE', $db->quoteIdentifier($map->firstName));

				if ($order === self::MERGE_NAMES_FIRST_LAST) {
					$params[] = $payer->firstName . ' ' . $payer->lastName;
				}
				else {
					$params[] = $payer->lastName . ' ' . $payer->firstName;
				}
			}
			else {
				$where = sprintf('%s = ? COLLATE U_NOCASE AND %s = ? COLLATE U_NOCASE', $db->quoteIdentifier($map->firstName), $db->quoteIdentifier($map->lastName));
				$params[] = $payer->firstName;
				$params[] = $payer->lastName;
			}
		}

		$sql = sprintf('SELECT id, %s AS identity FROM users WHERE %s;', $identity_field, $where);

		return $db->first($sql, ...$params);
	}

	public function getMappedUser(stdClass $payer): array
	{
		$out = [];
		$map = $this->config->fields_map ?? new stdClass;

		foreach ($map as $key => $target) {
			if (!$target) {
				continue;
			}

			if (!isset($payer->$key)) {
				continue;
			}

			$value = $payer->$key ?? null;

			if ($key === 'dateOfBirth' && $value) {
				$value = DateTime::createFromFormat('!Y-m-d', substr($value, 0, strlen(date('Y-m-d'))));
				$value = $value ? $value->format('d/m/Y') : '';
			}

			$out[$target] = $value;
		}

		if (isset($map->firstName, $map->lastName)
			&& $map->firstName === $map->lastName) {
			$order = $this->config->merge_names_order ?? self::MERGE_NAMES_FIRST_LAST;

			if ($order === self::MERGE_NAMES_FIRST_LAST) {
				$out[$map->firstName] = $payer->firstName . ' ' . $payer->lastName;
			}
			else {
				$out[$map->firstName] = $payer->lastName . ' ' . $payer->firstName;
			}
		}

		return $out;
	}

	static public function getPageSize(): int
	{
		return self::PER_PAGE;
	}


	static public function normalizeCustomFields(?array $fields): ?array
	{
		if ($fields === null) {
			return null;
		}

		$out = [];

		foreach ($fields as $field) {
			$out[$field->label] = $field->answer ?? null;
		}

		return $out;
	}
}

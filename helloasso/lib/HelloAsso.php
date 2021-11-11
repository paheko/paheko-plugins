<?php

namespace Garradin\Plugin\HelloAsso;

use Garradin\Config;
use Garradin\DB;
use Garradin\Plugin;
use Garradin\UserException;

use function Garradin\garradin_contributor_license;

class HelloAsso
{
	const CACHE_TABLE = 'plugin_helloasso_forms';
	const PER_PAGE = 20;

	const MERGE_NAMES_FIRST_LAST = 0;
	const MERGE_NAMES_LAST_FIRST = 1;

	const MERGE_NAMES_OPTIONS = [
		self::MERGE_NAMES_FIRST_LAST => 'Prénom Nom',
		self::MERGE_NAMES_LAST_FIRST => 'Nom Prénom',
	];

	protected $plugin;
	protected $config;
	protected $restricted;
	public $api;

	const FORM_TYPES = [
		'CrowdFunding' => 'Crowdfunding',
		'Membership'   => 'Adhésion',
		'Event'        => 'Billetteries',
		'Donation'     => 'Dons',
		'PaymentForm'  => 'Ventes',
		'Checkout'     => 'Encaissement',
		'Shop'         => 'Boutique',
	];

	const FORM_STATES = [
		'Draft'    => 'brouillon',
		'Public'   => 'public',
		'Private'  => 'privé',
		'Disabled' => 'désactivé',
	];

	const PAYMENT_STATES = [
		'Pending'               => 'À venir',
		'Authorized'            => 'Autorisé',
		'Refused'               => 'Refusé',
		'Unknown'               => 'Inconnu',
		'Registered'            => 'Enregistré',
		'Error'                 => 'Erreur',
		'Refunded'              => 'Remboursé',
		'Abandoned'             => 'Abandonné',
		'Refunding'             => 'En remboursement',
		'Canceled'              => 'Annulé',
		'Contested'             => 'Contesté',
		'WaitingBankValidation' => 'Attente de validation de la banque',
		'WaitingBankWithdraw'   => 'Attente retrait de la banque',
	];

	const PAYMENT_STATE_OK = 'Authorized';

	const PAYMENT_CASH_OUT_OK = 'CashedOut';

	const ORDER_STATUS_PAID = 1;
	const ORDER_STATUS_WAITING = 0;

	const PAYER_FIELDS = [
		'firstName' => 'Prénom',
		'lastName'  => 'Nom',
		'company'   => 'Organisme',
		'email'     => 'Adresse E-Mail',
		'address'   => 'Adresse postale',
		'zipCode'   => 'Code postal',
		'city'      => 'Ville',
		'country'   => 'Pays',
		'dateOfBirth' => 'Date de naissance',
	];

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
		$this->plugin = new Plugin('helloasso');
		$this->config = $this->plugin->getConfig();
		$this->api = new API($this);

		/**
		 * Merci de contribuer à Garradin pour obtenir une licence :)
		 */
		$level = 100;//garradin_contributor_license();

		if ($level < self::LEVEL_REQUIRED) {
			$this->restricted = true;
		}
		else {
			$this->restricted = false;
		}
	}

	public function isTrial(): bool
	{
		return $this->restricted;
	}

	public function getClientId(): ?string
	{
		return $this->config->client_id;
	}

	public function saveClient(string $client_id, string $client_secret): void
	{
		$this->config->client_id = trim($client_id);
		$this->api->createToken(trim($client_secret));
		$this->plugin->setConfig('client_id', $this->config->client_id);

		// Clear forms
		$db = DB::getInstance();
		$db->exec(sprintf('DELETE FROM %s;', self::CACHE_TABLE));
	}

	public function saveOAuth(\stdClass $data): void
	{
		$this->config->oauth = $data;
		$this->plugin->setConfig('oauth', $data);
	}

	public function getConfig(): ?\stdClass
	{
		return $this->config;
	}

	public function saveConfig(array $map, $merge_names, $match_email_field): void
	{
		$this->plugin->setConfig('merge_names', (int) $merge_names);
		$this->plugin->setConfig('match_email_field', (bool) $match_email_field);
		$this->plugin->setConfig('map_user_fields', $map);
		$this->config = $this->plugin->getConfig();
	}

	public function getOAuth(): ?\stdClass
	{
		return $this->config->oauth;
	}

	public function sync()
	{
		foreach ($targets as $target) {
			$target->sync($target);
		}
	}

	public function listTargets(): array
	{
		return EM::getInstance(Target::class, 'SELECT * FROM @TABLE ORDER BY label;');
	}

	public function getForm(int $id): \stdClass
	{
		return DB::getInstance()->first(sprintf('SELECT * FROM %s WHERE id = ?;', self::CACHE_TABLE), $id);
	}

	public function listForms(): array
	{
		$sql = sprintf('SELECT * FROM %s ORDER BY status = \'désactivé\', type, org_name COLLATE NOCASE, name COLLATE NOCASE;', self::CACHE_TABLE);
		$list = DB::getInstance()->get($sql);

		if (!count($list)) {
			$this->refreshForms();
			$list = DB::getInstance()->get($sql);
		}

		return $list;
	}

	public function refreshForms(): void
	{
		$organizations = $this->api->listOrganizations();
		$db = DB::getInstance();
		$db->exec(sprintf('DELETE FROM %s;', self::CACHE_TABLE));

		foreach ($organizations as $o) {
			$forms = $this->api->listForms($o->organizationSlug);

			foreach ($forms as $form) {
				$data = new \stdClass;
				$data->org_name = $o->name;
				$data->org_slug = $o->organizationSlug;

				$data->name = strip_tags($form->privateTitle ?? $form->title);
				$data->type = self::FORM_TYPES[$form->formType] ?? 'Inconnu';
				$data->status = self::FORM_STATES[$form->state] ?? 'Inconnu';
				$data->form_type = $form->formType;
				$data->form_slug = $form->formSlug;

				$db->insert(self::CACHE_TABLE, $data);
			}
		}
	}

	public function listPayments(\stdClass $form, int $page = 1, &$count = null): array
	{
		$per_page = self::PER_PAGE;

		if ($this->isTrial()) {
			$per_page = self::PER_PAGE_TRIAL;
			$page = 1;
		}

		$result = $this->api->listFormPayments($form->org_slug, $form->form_type, $form->form_slug, $page, $per_page);

		$count = $result->pagination->totalCount;

		foreach ($result->data as &$row) {
			$row = $this->transformPayment($row);
		}

		unset($row);

		return $result->data;
	}

	public function listOrganizationPayments(string $org_slug, int $page = 1, &$count = null): array
	{
		$per_page = self::PER_PAGE;

		if ($this->isTrial()) {
			$per_page = self::PER_PAGE_TRIAL;
			$page = 1;
		}

		$result = $this->api->listOrganizationPayments($org_slug, $page, $per_page);

		$count = $result->pagination->totalCount;

		foreach ($result->data as &$row) {
			$row = $this->transformPayment($row);
		}

		unset($row);

		return $result->data;
	}

	public function listOrganizationOrders(string $org_slug, int $page = 1, &$count = null): array
	{
		$per_page = self::PER_PAGE;

		if ($this->isTrial()) {
			$per_page = self::PER_PAGE_TRIAL;
			$page = 1;
		}

		$result = $this->api->listOrganizationOrders($org_slug, $page, $per_page);

		$count = $result->pagination->totalCount;

		foreach ($result->data as &$row) {
			$row = $this->transformOrder($row);
		}

		unset($row);

		return $result->data;
	}

	public function listFormOrders(\stdClass $form, int $page = 1, &$count = null): array
	{
		$per_page = self::PER_PAGE;

		if ($this->isTrial()) {
			$per_page = self::PER_PAGE_TRIAL;
			$page = 1;
		}

		$result = $this->api->listFormOrders($form->org_slug, $form->form_type, $form->form_slug, $page, $per_page);

		$count = $result->pagination->totalCount;

		foreach ($result->data as &$row) {
			$row = $this->transformOrder($row);
		}

		unset($row);

		return $result->data;
	}

	protected function transformOrder(\stdClass $order)
	{
		$order->date = new \DateTime($order->date);
		$order->payer_name = $this->getPayerName($order->payer);
		$order->status = $this->getOrderStatus($order);
		$order->payer_infos = $this->getPayerInfos($order->payer);

		if (isset($order->payments)) {
			foreach ($order->payments as &$payment) {
				$payment = $this->transformPayment($payment);
			}

			unset($payment);
		}

		$order->items = $this->transformItems($order->items);

		unset($item);

		return $order;
	}

	protected function getPayerInfos(\stdClass $payer)
	{
		$data = [];

		foreach (self::PAYER_FIELDS as $key => $name) {
			if (!isset($payer->$key)) {
				continue;
			}

			$value = $payer->$key;

			if ($key == 'dateOfBirth') {
				$value = new \DateTime($value);
			}

			$data[$name] = $value;
		}

		return $data;
	}

	protected function getOrderStatus(\stdClass $order)
	{
		$total = $order->amount->total;
		$paid = 0;

		if (isset($order->payments)) {
			foreach ($order->payments as $payment) {
				if ($payment->state == self::PAYMENT_STATE_OK) {
					$paid += $payment->amount;
				}
			}
		}

		return $paid >= $total ? self::ORDER_STATUS_PAID : self::ORDER_STATUS_WAITING;
	}

	public function getPayerName(\stdClass $payer)
	{
		$names = [!empty($payer->company) ? $payer->company . ' — ' : null, $payer->firstName ?? null, $payer->lastName ?? null];
		$names = array_filter($names);

		$names = implode(' ', $names);

		if (!empty($payer->city)) {
			$names .= sprintf(' (%s)', $payer->city);
		}

		return $names;
	}

	public function getOrder(string $id): \stdClass
	{
		$data = $this->api->getOrder($id);
		return $this->transformOrder($data);
	}

	public function getPayment(string $id): \stdClass
	{
		$data = $this->api->getPayment($id);
		return $this->transformPayment($data);
	}

	public function transformItems(array $items): array
	{
		$out = [];

		foreach ($items as $item) {
			$item->user_name = isset($item->user) ? $this->getPayerName($item->user) : '';

			if ($item->type == 'Donation') {
				$item->name = 'Don';
			}

			$item->type_name = self::FORM_TYPES[$item->type] ?? '';

			$out[] = $item;

			if (!isset($item->options)) {
				continue;
			}

			foreach ($item->options as $option) {
				$option->type_name = 'Option';
				$out[] = $option;
			}
		}

		return $out;
	}

	public function transformPayment(\stdClass $payment): \stdClass
	{
		$payment->order_id = $payment->order->id ?? null;
		$payment->date = new \DateTime($payment->date);
		$payment->status = self::PAYMENT_STATES[$payment->state] ?? '--';
		$payment->transferred = isset($payment->cashOutState) && $payment->cashOutState == self::PAYMENT_CASH_OUT_OK ? true : false;

		$payment->payer_infos = isset($payment->payer) ? $this->getPayerInfos($payment->payer) : null;

		return $payment;
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

	static public function cron() {
	}

	/**
	 * Toutes ces lignes de code ne se sont pas écrites toutes seules…
	 * Merci de contribuer à Garradin ;)
	 */
	const LEVEL_REQUIRED = 50;
	const PER_PAGE_TRIAL = 5;
}

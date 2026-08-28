<?php

namespace Paheko\Plugin\Invoice\API\Providers\SuperPDP;

use Paheko\Config;
use Paheko\Utils;
use Paheko\Security;

use KD2\HTTP\OAuth\Client;

use const Paheko\SUPERPDP_CLIENT_ID;
use const Paheko\SUPERPDP_CLIENT_SECRET;

class SuperPDP extends AbstractProvider
{
	const NAME = 'SuperPDP';

	const API_URL = 'https://api.superpdp.tech/';
	const AUTH_URL = self::API_URL . 'oauth2/authorize';
	const TOKEN_URL = self::API_URL . 'oauth2/token';

	protected string $afnor_api_url = 'https://api.superpdp.tech/afnor-flow/v1/';

	protected ?string $access_token = null;
	protected ?string $refresh_token = null;
	protected ?int $expiry = null;

	protected Plugin $plugin;

	static public function hasFactorySettings(): bool
	{
		return defined('Paheko\SUPERPDP_CLIENT_SECRET') && defined('Paheko\SUPERPDP_CLIENT_ID');
	}

	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
		$config = $this->plugin->getConfig();

		if (isset($config->access_token, $config->refresh_token, $config->expiry)) {
			$this->access_token =  Security::decryptWithPassword(null, $config->superpdp_access_token);
			$this->refresh_token =  Security::decryptWithPassword(null, $config->superpdp_refresh_token);
			$this->expiry =  $config->superpdp_expiry;
		}
	}

	public function setClient(
		string $id,
		#[\SensitiveParameter]
		string $secret
	): void
	{
		$oauth = new Client(self::TOKEN_URL, $id, $secret);
		$r = $oauth->fetchToken();
		$this->plugin->setConfigProperty('superpdp_access_token', Security::encryptWithPassword(null, $r->access_token));
		$this->plugin->setConfigProperty('superpdp_expiry', time() + ($r->expires_in ?? 3000));
		$this->plugin->setConfigProperty('superpdp_refresh_token', Security::encryptWithPassword(null, $r->refresh_token));
		$this->plugin->save();
	}

	/**
	 * Return TRUE is auth was done, or FALSE if user is redirected, and script execution should stop
	 */
	public function startFactoryAuth(): bool
	{
		$url = Utils::getSelfURI();
		$oauth = new Client(self::TOKEN_URL, SUPERPDP_CLIENT_ID, SUPERPDP_CLIENT_SECRET, self::AUTH_URL, $url);

		if (isset($_GET['code'])) {
			$session->start(true);
			$r = $oauth->handleResponse($_SESSION);
			$session->close();

			$this->plugin->setConfigProperty('superpdp_access_token', Security::encryptWithPassword(null, $r->access_token));
			$this->plugin->setConfigProperty('superpdp_expiry', time() + ($r->expires_in ?? 3000));
			$this->plugin->setConfigProperty('superpdp_refresh_token', Security::encryptWithPassword(null, $r->refresh_token));
			$this->plugin->save();
			return true;
		}

		$config = Config::getInstance();
		$session->start(true);
		$url = $oauth->getAuthorizationURL($_SESSION, [
			'login_hint' => $config->org_email,
			'superpdp_company_number' => substr($config->org_business_number ?? '', 0, 9),
			'superpdp_company_number_scheme' => 'fr_siren',
		]);
		$session->close();

		Utils::redirect($url);
		return false;
	}

	public function getCredit(): ?int
	{
		if (!Plugins::hasSignal('superpdp.credit.get')) {
			return null;
		}

		$signal = Plugins::fire('superpdp.credit.get');
		return $signal->getOut('credit') ?? 0;
	}

	public function consumeCredits(int $credits): void
	{
		if (!Plugins::hasSignal('superpdp.credit.consume')) {
			return;
		}

		Plugins::fire('superpdp.credit.consume', false, compact('credits'));
	}

	public function API(string $method, string $url, ?string $type = null, $data = null): stdClass
	{
		if ($this->expiry <= time()) {
			$this->refreshToken();
		}

		$http = new HTTP;
		$http->headers['Authorization'] = 'Bearer ' . $this->access_token;

		if ($type !== null) {
			$http->headers['Content-Type'] = $type;
		}

		$r = $http->request($method, $url, $data);

		if ($r->status !== 200) {
			throw new \LogicException('API error: ' . $r->status . "\n" . $r->body);
		}

		$data = json_decode($r->body);
		return $data;
	}

	public function send(Invoice $invoice): void
	{
		$export = $invoice->exportAs('cii');
		$url = sprintf('v1.beta/invoices?external_id=%d', $invoice->id);

		$r = $this->api('POST', $url, 'text/xml', $export);

		$invoice->set('date_sent', new \DateTime($r->created_at));
		$invoice->set('provider_id', $r->id);
		$invoice->set('provider_name', self::NAME);
		$invoice->saveOnly(['date_sent', 'provider_id', 'provider_name']);

		foreach ($r->events ?? [] as $e) {
			$invoice->logEvent(new \DateTime($e->created_at), $e->status_code, $e->status_text, json_encode($e));
		}
	}

	public function fetch(): void
	{
		$params = [
			'direction' => 'in',
			'order' => 'asc',
			'expand' => ['en_invoice'],
			'limit' => 100,
		];

		if ($id = $this->plugin->getConfig('last_received_invoice_id')) {
			$params['starting_after_id'] = $id;
		}

		$url = sprintf('v1.beta/invoices?%s', http_build_query($params));
		$r = $this->api('GET', $url);

		foreach ($r->data ?? [] as $data) {
			$i = Invoices::receive($data->en_invoice);
			$i->provider_id = $data->id;
			$i->provider_name = self::NAME;

			$last_id = $data->id;
		}

		if ($last_id) {
			$this->plugin->setConfigProperty('superpdp_last_received_invoice_id');
		}

		$this->fetchEvents();

		$this->plugin->save();
	}

	public function fetchEvents(): void
	{
		$params = [
			'order' => 'asc',
			'limit' => 500,
		];

		if ($id = $this->plugin->getConfig('last_received_event_id')) {
			$params['starting_after_id'] = $id;
		}

		$url = sprintf('v1.beta/invoices_events?%s', http_build_query($params));
		$r = $this->api('GET', $url);

		foreach ($r->data ?? [] as $data) {
			Invoices::receive($data->en_invoice);
		}

	}
}

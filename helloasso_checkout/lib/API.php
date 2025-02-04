<?php

namespace Paheko\Plugin\HelloAsso_Checkout;

use Paheko\UserException;

use KD2\HTTP;
use KD2\DB\EntityManager;

class API
{
	const BASE_URL = 'https://api.helloasso.com/';
	const SANDBOX_BASE_URL = 'https://api.helloasso-sandbox.com/';

	protected $ha;
	protected $oauth;
	protected $client_id;
	protected $org_slug;
	protected $sandbox;

	static protected $_instance = null;

	static public function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	static public function refreshTokenIfExipired()
	{
		$instance = self::getInstance();
		if ($instance->ha->isConfigured()) {
			try {
				$instance->getToken();
			} catch (UserException $e) {
			}
		}
	}

	protected function __construct()
	{
		$this->ha = HelloAsso::getInstance();
		$this->oauth = $this->ha->plugin()->getConfig('oauth');
		$this->client_id = $this->ha->plugin()->getConfig('client_id');
		$this->org_slug = $this->ha->plugin()->getConfig('org_slug');
		$this->sandbox = $this->ha->plugin()->getConfig('sandbox');
	}

	private function __clone()
	{
	}

	protected function GET(string $url, array $data = [])
	{
		return $this->request('GET', $url, $data);
	}

	protected function POST(string $url, array $data = [], string $format = HTTP::FORM)
	{
		return $this->request('POST', $url, $data, $format);
	}

	protected function request(string $type, string $url, array $data = [], string $format = HTTP::FORM)
	{
		$allowed_formats = [HTTP::FORM, HTTP::JSON];
		$url = $this->getUrl($url);

		$token = $this->getToken();

		$headers = [
			'Authorization' => sprintf('Bearer %s', $token),
			'Accept' => 'application/json',
			'User-Agent' => 'Paheko',
		];

		if ($type == 'GET') {
			if ($data) {
				$url .= '?' . http_build_query($data);
			}

			$response = (new HTTP)->GET($url, $headers);
		} else {
			if (!in_array($format, $allowed_formats)) {
				throw new \InvalidArgumentException(sprintf('Wrong request format: %s. Allowed formats are: %s.', $format, implode(', ', $allowed_formats)));
			}
			$response = (new HTTP)->POST($url, $data, $format, $headers);
		}

		if ($response->fail || $response->status != 200) {
			$error = sprintf('%d - %s', $response->status, $response->body ?: $response->error);
			throw new UserException('Erreur de l\'API HelloAsso : ' . $error);
		}

		$data = json_decode($response->body);

		if (null === $data) {
			throw new UserException('Erreur de l\'API HelloAsso, réponse illisible : ' . $response->body);
		}

		return $data;
	}

	/******* OAUTH METHODS ********/
	protected function getToken(): string
	{
		if (empty($this->oauth->access_token) || empty($this->oauth->expiry)) {
			throw new UserException('Authentification à l\'API impossible, merci de renseigner les informations de connexion à l\'API dans la configuration.');
		} elseif ($this->oauth->expiry - 10 <= time()) {
			$this->oauth = $this->refreshToken($this->oauth->refresh_token);
			$this->ha->plugin()->setConfigProperty('oauth', $this->oauth);
		}

		return $this->oauth->access_token;
	}

	public function register(string $client_id, string $secret): bool
	{
		$this->client_id = trim($client_id);
		$this->oauth = $this->createToken(trim($secret));

		$organizations = $this->listOrganizations();
		if (count($organizations)) {
			$this->org_slug = $organizations[0]->organizationSlug;
			$this->ha->plugin()->setConfigProperty('org_slug', $this->org_slug);
		}

		$this->ha->plugin()->setConfigProperty('client_id', $this->client_id);
		$this->ha->plugin()->setConfigProperty('oauth', $this->oauth);

		return $this->ha->plugin()->save();
	}

	public function createToken(string $secret): \stdClass
	{
		$params = [
			'grant_type' => 'client_credentials',
			'client_id' => $this->client_id,
			'client_secret' => $secret,
		];

		return $this->requestToken($params);
	}

	protected function refreshToken(string $token): \stdClass
	{
		$params = [
			'grant_type' => 'refresh_token',
			'client_id' => $this->client_id,
			'refresh_token' => $token,
		];

		return $this->requestToken($params);
	}

	protected function requestToken(array $params): \stdClass
	{
		$url = $this->getUrl('oauth2/token');

		$response = (new HTTP)->POST($url, $params);

		if ($response->fail || $response->status != 200) {
			$error = sprintf('%d - %s', $response->status, $response->body ?: $response->error);
			throw new UserException('Erreur de l\'API HelloAsso : ' . $error);
		}

		$oauth = json_decode($response->body);

		if (null === $oauth) {
			throw new UserException('Erreur de l\'API HelloAsso, réponse illisible : ' . $response->body);
		}

		if (!isset($oauth->access_token, $oauth->refresh_token, $oauth->expires_in, $oauth->token_type)) {
			throw new UserException('Erreur de l\'API HelloAsso à l\'authentification, essayez de supprimer puis de remettre les informations de connexion à l\'API dans la configuration.');
		}

		$oauth->expiry = time() + $oauth->expires_in;

		return $oauth;
	}

	protected function assert($condition)
	{
		if (!$condition) {
			throw new \RuntimeException('Données manquantes depuis HelloAsso !');
		}
	}

	public function listOrganizations(): array
	{
		$result = $this->GET('v5/users/me/organizations');

		$this->assert(is_array($result));

		if (count($result)) {
			$r = $result[0];
			$this->assert(isset($r->name));
			$this->assert(isset($r->organizationSlug));
		}

		return $result;
	}

	public function listForms(): array
	{
		if (!preg_match('/^[a-z0-9_-]+$/', $this->org_slug)) {
			throw new \RuntimeException('Invalid organization slug');
		}

		$params = ['pageSize' => 100];

		$result = $this->GET(sprintf('v5/organizations/%s/forms', $this->org_slug), $params);

		$this->assert(isset($result->data));
		$this->assert(is_array($result->data));

		if (count($result->data)) {
			$r = $result->data[0];
			$this->assert(isset($r->title));
			$this->assert(isset($r->formType));
			$this->assert(isset($r->formSlug));
			$this->assert(isset($r->state));
		}

		return $result->data;
	}

	public function listOrganizationOrders(array $params = []): \stdClass
	{
		if (!preg_match('/^[a-z0-9_-]+$/', $this->org_slug)) {
			throw new \RuntimeException('Invalid organization slug');
		}

		$params['withDetails'] = 'true';

		$result = $this->GET(sprintf('v5/organizations/%s/orders', $this->org_slug), $params);

		$this->assertOrders($result);

		return $result;
	}

	public function assertOrders(\stdClass $result)
	{
		$this->assert(isset($result->data));
		$this->assert(is_array($result->data));
		$this->assert(isset($result->pagination->totalCount));

		if (count($result->data)) {
			$r = $result->data[0];
			$this->assert(isset($r->date));
			$this->assert(strtotime($r->date));
			$this->assert(isset($r->id));
			$this->assert(!isset($r->amount->total) || ctype_digit((string) $r->amount->total)); // This can be empty if it's free
		}
	}

	public function listOrderItems(int $order_id): \stdClass
	{
		$result = $this->GET(sprintf('v5/orders/%s', $order_id));

		return $result;
	}

	public function listOrganizationItems(array $params = []): \stdClass
	{
		if (!preg_match('/^[a-z0-9_-]+$/', $this->org_slug)) {
			throw new \RuntimeException('Invalid organization slug');
		}

		$params['withDetails'] = 'true';

		$result = $this->GET(sprintf('v5/organizations/%s/items', $this->org_slug), $params);

		$this->assertItems($result);

		return $result;
	}

	protected function assertItems($result)
	{
		$this->assert(isset($result->data));
		$this->assert(is_array($result->data));
		$this->assert(isset($result->pagination->totalCount));

		if (count($result->data)) {
			$r = $result->data[0];
			$this->assert(isset($r->order->id));
			$this->assert(isset($r->state));
			$this->assert(isset($r->type));
			$this->assert(isset($r->id));
			$this->assert(isset($r->amount) && ctype_digit((string) $r->amount)); // int between -128 and 255 is interpreted as the ASCII value of a single character
		}
	}

	public function getCheckout(int $id): \stdClass
	{
		$result = $this->GET('v5/organizations/' . $this->org_slug . '/checkout-intents/' . (int) $id);

		$this->assertCheckout($result);

		return $result;
	}

	protected function assertCheckout(\stdClass $result): void
	{
		$this->assert(isset($result->id));
		$this->assert(isset($result->redirectUrl));
		//$this->assert(isset($result->metadata));
		//$this->assert(is_object($result->metadata));

		if (isset($result->order)) {
			$this->assert(isset($result->order->date));
			$this->assert(is_string($result->order->date));
			$this->assert(isset($result->order->payer));
			$this->assert(isset($result->order->items));
			$this->assert(is_array($result->order->items));
			$this->assert(isset($result->order->items[0]->name));
		}
	}

	public function createCheckout(int $amount, string $label, string $query, array $payer = null): \stdClass
	{
		$base_url = HTTP::getScheme() == 'https' ? HTTP::getRequestURL(false) : "https://lesptitsvelos.fr";

		$params = [
			'totalAmount' => $amount,
			'initialAmount' => $amount,
			'itemName' => $label,
			'backUrl' => "$base_url?$query&status=canceled",
			'returnUrl' => "$base_url?$query&status=success",
			'errorUrl' => "$base_url?$query&status=error",
			'containsDonation' => false,
		];
		if ($payer != null)
			$params['payer'] = $payer;

		$response = $this->POST(sprintf('v5/organizations/%s/checkout-intents', $this->org_slug), $params, HTTP::JSON);

		if (!isset($response->id, $response->redirectUrl)) {
			throw new \RuntimeException('Erreur API HelloAsso: id ou redirectUrl manquants: ' . json_encode($response));
		}

		return (object) [
			'id' => (string) $response->id,
			'url' => $response->redirectUrl
		];
	}

	protected function getUrl($path = ''): string
	{
		return ($this->sandbox ? self::SANDBOX_BASE_URL : self::BASE_URL) . $path;
	}
}
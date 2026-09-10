<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Config;
use Paheko\Entity;
use Paheko\Utils;
use Paheko\Users\DynamicFields;
use Paheko\Users\Users;

use DateTime;
use stdClass;

class Client extends Entity
{
	const TABLE = 'plugin_invoice_clients';

	protected ?int $id = null;
	protected ?int $id_user = null;

	protected bool $archived = false;
	protected string $name;
	protected string $country;
	protected string $address;
	protected string $post_code;
	protected string $city;
	// BR-FR-12/BT-49 : Le BT-49 est obligatoire.
	protected string $email;
	protected ?string $phone;
	protected ?string $notes;

	/**
	 * Code SIRET si country == 'FR'
	 */
	protected ?string $business_number;

	protected ?string $vat_number;

	protected bool $e_invoicing = false;
	protected ?string $electronic_address;

	protected bool $self_billing = false;

	protected DateTime $created;

	const SCHEMES = [
		'0002' => 'SIREN',
		'0009' => 'SIRET',
		'0183' => 'IDE', // Suisse
		'0208' => 'BCE', // Belgique
		'0223' => 'Numéro TVA', // Europe
		'0225' => 'France (PPF)',
	];

	const E_EINVOCING_COUNTRIES = [
		'BE',
		'FR',
	];

	const EU_COUNTRIES = [
		"AT",
		"BE",
		"BG",
		"HR",
		"CY",
		"CZ",
		"DK",
		"EE",
		"FI",
		"FR",
		"DE",
		"GR",
		"HU",
		"IE",
		"IT",
		"LV",
		"LT",
		"LU",
		"MT",
		"NL",
		"PL",
		"PT",
		"RO",
		"SK",
		"SI",
		"ES",
		"SE",
	];

	public function selfCheck(): void
	{
		$this->assert(mb_strlen(trim($this->name)), 'Le nom est vide');
		$this->assert(strlen($this->country) === 2, 'Le pays est vide ou invalide');
		$this->assert(Utils::getCountryName($this->country) !== null, 'Le pays est invalide');
		$this->assert(mb_strlen($this->name) <= 500, 'Le nom ne peut faire plus de 500 caractères');
		$this->assert(isset($this->address) && mb_strlen($this->address), 'L\'adresse postale n\'est pas renseignée');
		$this->assert(mb_strlen($this->address) <= 5000, 'L\'adresse ne peut faire plus de 5000 caractères');
		$this->assert(isset($this->post_code) && mb_strlen($this->post_code), 'Le code postal n\'est pas renseigné');
		$this->assert(!isset($this->phone) || mb_strlen($this->phone) <= 100, 'Le numéro de téléphone ne peut faire plus de 100 caractères');
		$this->assert(isset($this->email) && mb_strlen($this->email), 'L\'adresse e-mail est obligatoire');
		$this->assert(mb_strlen($this->email) <= 1000, 'L\'adresse e-mail ne peut faire plus de 1000 caractères');
		$this->assert(!isset($this->notes) || mb_strlen($this->notes) <= 10000, 'Les notes ne peuvent faire plus de 10.000 caractères');
		$this->assert(!isset($this->business_number) || mb_strlen($this->business_number) <= 100, 'Le numéro d\'entreprise ne peut faire plus de 100 caractères');
		$this->assert(!isset($this->vat_number) || mb_strlen($this->vat_number) <= 100, 'Le numéro de TVA ne peut faire plus de 100 caractères');

		if ($this->country === 'FR' && isset($this->business_number)) {
			$this->assert(strlen($this->business_number) === 14, 'Le numéro de SIRET doit faire 14 chiffres');
			$this->assert(Utils::verifyBusinessNumber($this->country, $this->business_number), 'Le numéro de SIRET est invalide : ' . $this->business_number);
		}

		if (isset($this->electronic_address)) {
			$this->assert(preg_match('/^\d{4}:/', $this->electronic_address), 'L\'adresse de facturation électronique est invalide : elle doit commencer par 4 chiffres, suivi du caractère deux points.');

			if ($this->country === 'FR') {
				$prefix = strtok($this->electronic_address, ':');
				$siren = strtok('_');
				$other = strtok('');

				$this->assert(in_array($prefix, ['0002', '0009', '0225']), 'Adresse de facturation électronique invalide : elle doit commencer par 0002, 0009 ou 0225.');
				$this->assert(Utils::verifyBusinessNumber($this->country, $siren), 'Adresse de facturation électronique invalide : elle doit comporter un SIREN valide.');
			}
		}

		if ($this->e_invoicing) {
			$this->assert($this->business_number, 'La facturation électronique ne peut être activée sans numéro d\'entreprise');
		}

		parent::selfCheck();
	}

	public function reloadUserData(): void
	{
		if (!$this->id_user) {
			return;
		}

		$user = Users::get($this->id_user);

		if (!$user) {
			$this->set('id_user', null);
		}

		$config = Config::getInstance();

		$this->assert(DynamicFields::get('adresse'), 'Il n\'y a pas de champ nommé "adresse" dans les fiches de membre. Merci d\'en créer un.');
		$this->assert(DynamicFields::get('code_postal'), 'Il n\'y a pas de champ nommé "code_postal" dans les fiches de membre. Merci d\'en créer un.');
		$this->assert(DynamicFields::get('ville'), 'Il n\'y a pas de champ nommé "ville" dans les fiches de membre. Merci d\'en créer un.');

		$data = [
			'name'      => $user->name(),
			'country'   => $user->pays ?? $config->country,
			'address'   => $user->adresse ?? null,
			'post_code' => $user->code_postal ?? null,
			'city'      => $user->ville ?? null,
			'email'     => $user->email(),
			'phone'     => $user->telephone ?? null,
		];

		$data = array_filter($data, fn($v) => $v !== null);

		$this->importForm($data);
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['archived_present'])) {
			$source['archived'] = !empty($source['archived']);
		}

		$country = $source['country'] ?? $this->country;

		if ($country === 'FR'
			&& isset($source['fr_business_number'])) {
			$source['business_number'] = Utils::normalizeBusinessNumber($country, $source['fr_business_number']);
			$source['vat_number'] = $source['fr_vat_number'] ?? null;
		}

		if (isset($source['electronic_address'])) {
			$address = trim($source['electronic_address']);
			$address = preg_replace('/\s+/', '', $address);

			if ($country === 'FR' && ctype_digit($address)) {
				$address = Utils::normalizeBusinessNumber($country, $address);
			}

			$source['electronic_address'] = $address;
		}


		return parent::importForm($source);
	}

	public function isBusiness(): bool
	{
		return isset($this->business_number) || isset($this->vat_number);
	}

	public function requiresEInvoicing(): bool
	{
		return $this->e_invoicing;
	}

	public function exportForInvoice(): stdClass
	{
		return self::exportPersonForInvoice($this);
	}

	public function getScheme(string $country, string $business_number): string
	{
		if ($country === 'FR') {

		}
	}

	/**
	 * Return client as an object ready for EN16931
	 */
	static public function exportPersonForInvoice(stdClass|Client $person): stdClass
	{
		$lines = explode("\n", $person->address ?? '');
		$is_eu = in_array($person->country, self::EU_COUNTRIES);

		$default = (object) ['scheme' => null, 'value' => null];

		// BT-29 (seller) / BT-46 (buyer)
		// A seller identifier with a scheme identifier can be used. Examples: DUNS, GLN, etc.
		// /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:GlobalID
		// /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:GlobalID
		// SuperPDP = identifiers
		// FR = SIRET
		$identifier = $default;

		// BT-30 / BT-47
		// legal registration identifier
		// An identifier issued by an official registrar that identifies the seller as a legal entity or person.
		// /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:SpecifiedLegalOrganization/ram:ID
		// /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:SpecifiedLegalOrganization/ram:ID
		// SuperPDP = legal_registration_identifier
		// FR = SIREN
		$legal_registration_identifier = $default;

		// BT-34, BT-49
		// electronic address
		// Identifies the Seller's electronic address to which the application level response to the invoice may be delivered.
		// /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:URIUniversalCommunication/ram:URIID
		// /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:URIUniversalCommunication/ram:URIID
		// SuperPDP = electronic_address
		// FR = SIREN
		$electronic_address = $default;

		// See https://docs.peppol.eu/poacc/billing/3.0/codelist/ICD/ for scheme list
		if (isset($person->electronic_address)) {
			$default->scheme = strtok(':');
			$default->value = strtok('');

			// Different object
			$electronic_address = clone $default;
		}

		if ($person->country === 'FR') {
			$siren = substr($person->business_number, 0, 9);

			// Always the SIREN by default
			$default->scheme = '0002';
			$default->value = $siren;

			if (!isset($electronic_address->value)) {
				// 0225 is FRCTC ELECTRONIC ADDRESS (internal France directory)
				// because using 0002 would have been too simple I guess
				$electronic_address = (object) ['scheme' => '0225', 'value' => $siren];
			}

			// If we have the SIRET, use it in BT-29 / BT-46 (required by Chorus Pro)
			if (strlen($person->business_number) > 9) {
				$identifier = clone $default;
				$identifier->scheme = '0009';
				$identifier->value = $person->business_number;
			}
		}
		elseif ($person->country === 'CH') {
			// Numéro IDE
			$default->scheme = '0183';
			$default->value = $person->business_number;
		}
		elseif ($person->country === 'BE') {
			// Numéro BCE
			$default->scheme = '0208';
			$default->value = $person->business_number;
		}
		elseif ($is_eu) {
			// VAT number
			$default->scheme = '0223';
			$default->value = $person->vat_number;
		}
		else {
			// Outside of EU
			$default->scheme = '0227';
			$default->value = $person->business_number;
		}

		return (object) [
			'identifiers' => ['items' => [$identifier]],
			'legal_registration_identifier' => $legal_registration_identifier,
			'electronic_address' => $electronic_address,
			'name' => $person->name,
			'postal_address' => (object) [
				'country_code' => $person->country,
				'address_line1' => $lines[0] ?? '',
				'address_line2' => $lines[1] ?? '',
				'address_line3' => implode("\n", array_slice($lines, 2)),
				'city' => $person->city ?? '',
				'post_code' => $person->post_code ?? '',
			],
			'contact' => (object) [
				'email_address' => $person->email,
				'phone_number' => $person->phone,
			],
			'vat_identifier' => $person->vat_number,
		];
	}
}

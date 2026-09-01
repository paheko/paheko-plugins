<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Entity;
use Paheko\Utils;

use DateTime;
use stdClass;

class Client extends Entity
{
	const TABLE = 'plugin_invoice_clients';

	protected ?int $id = null;
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
		parent::selfCheck();

		$this->assert(mb_strlen(trim($this->name)), 'Le nom est vide');
		$this->assert(strlen($this->country) === 2, 'Le pays est vide ou invalide');
		$this->assert(Utils::getCountryName($this->country) !== null, 'Le pays est invalide');
		$this->assert(mb_strlen($this->name) <= 500, 'Le nom ne peut faire plus de 500 caractères');
		$this->assert(!isset($this->address) || mb_strlen($this->address) <= 5000, 'L\'adresse ne peut faire plus de 5000 caractères');
		$this->assert(!isset($this->phone) || mb_strlen($this->phone) <= 100, 'Le numéro de téléphone ne peut faire plus de 100 caractères');
		$this->assert(!isset($this->email) || mb_strlen($this->email) <= 1000, 'L\'adresse e-mail ne peut faire plus de 1000 caractères');
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
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['archived_present'])) {
			$source['archived'] = !empty($source['archived']);
		}

		$country = $source['country'] ?? $this->country;

		if ($country === 'FR') {
			if (isset($source['e_invoicing']) && empty($source['e_invoicing'])) {
				$source['business_number'] = $source['vat_number'] = '';
			}
			elseif (isset($source['fr_business_number'])) {
				$source['business_number'] = Utils::normalizeBusinessNumber($country, $source['fr_business_number']);
				$source['vat_number'] = $source['fr_vat_number'] ?? null;
			}

			if (isset($source['electronic_address'])) {
				$address = trim($source['electronic_address']);
				$address = preg_replace('/\s+/', '', $address);

				if (ctype_digit($address)) {
					$address = Utils::normalizeBusinessNumber($country, $address);
				}

				$source['electronic_address'] = $address;
			}
		}

		return parent::importForm($source);
	}

	public function isBusiness(): bool
	{
		return isset($this->business_number) || isset($this->vat_number);
	}

	public function requiresEInvoicing(): bool
	{
		return in_array($this->country, self::E_EINVOCING_COUNTRIES, true) && (isset($this->business_number) || isset($this->vat_number));
	}

	public function exportForInvoice(): stdClass
	{
		return self::exportPersonForInvoice($this);
	}

	/**
	 * Return client as an object ready for EN16931
	 */
	static public function exportPersonForInvoice(stdClass|Client $person): stdClass
	{
		$lines = explode("\n", $person->address ?? '');
		$is_eu = in_array($person->country, self::EU_COUNTRIES);
		$e_scheme = $e_value = null;

		if (isset($person->electronic_address)) {
			$e_scheme = strtok(':');
			$e_value = strtok('');
		}

		// See https://docs.peppol.eu/poacc/billing/3.0/codelist/ICD/
		if ($person->country === 'FR') {
			$e_scheme ??= '0225';

			// Always the SIREN
			$scheme = '0002';
			$value = substr($person->business_number, 0, 9);
		}
		elseif ($person->country === 'CH') {
			// Numéro IDE
			$scheme = '0183';
			$value = $person->business_number;
		}
		elseif ($person->country === 'BE') {
			// Numéro BCE
			$scheme = '0208';
			$value = $person->business_number;
		}
		elseif ($is_eu) {
			// VAT number
			$scheme = '0223';
			$value = $person->vat_number;
		}
		else {
			// Outside of EU
			$scheme = '0227';
			$value = $person->business_number;
		}

		$e_scheme ??= $scheme;
		$e_value ??= $value;

		return (object) [
			'electronic_address' => ['scheme' => $e_scheme, 'value' => $e_value],
			'legal_registration_identifier' => (object) compact('scheme', 'value'),
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

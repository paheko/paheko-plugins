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
	protected ?string $address;
	protected ?string $post_code;
	protected ?string $city;
	protected ?string $phone;
	protected ?string $email;
	protected ?string $notes;

	/**
	 * Code SIREN si country == 'FR'
	 */
	protected ?string $business_number;

	protected ?string $vat_number;
	protected DateTime $created;

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
			$this->assert(strlen($this->business_number) === 9, 'Le numéro de SIREN doit faire 9 caractères');
			$this->assert(self::verifySIREN($this->business_number), 'Le numéro de SIREN est invalide');
		}
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['archived_present'])) {
			$source['archived'] = !empty($source['archived']);
		}

		return parent::importForm($source);
	}

	public function save(bool $selfcheck = true): bool
	{
		// Make sure we get the SIREN number even if we have been supplied with the SIRET
		if ($this->country === 'FR'
			&& isset($this->business_number)
			&& strlen($this->business_number) > 9) {
			$this->business_number = substr($this->business_number, 0, 9);
		}

		return parent::save($selfcheck);
	}

	static public function verifySIREN(string $number): bool
	{
		$total = 0;

		for ($i = 0; $i < 9; $i++) {
			$digit = (int)$number[$i];

			// Every even digit is doubled
			if ($i % 2 == 1) {
				$digit *= 2;

				if ($digit > 9) {
					$digit -= 9;
				}
			}

			$total += $digit;
		}

		// Sum must be divisable by 10
		return $total % 10 === 0;
	}

	public function exportForInvoice(): array
	{
		return self::exportPersonForInvoice($this);
	}

	/**
	 * Return client as an object ready for EN16931
	 */
	static public function exportPersonForInvoice(stdClass|Client $person): array
	{
		$address = explode("\n", $person->address ?? '');
		$is_eu = in_array($person->country, self::EU_COUNTRIES);
		// See https://docs.peppol.eu/poacc/billing/3.0/codelist/ICD/
		$scheme = $person->country === 'FR' ? '0002' : ($is_eu ? '0223' : '0227');
		$value = $person->country === 'FR' || !$is_eu ? $person->business_number : $person->vat_number;

		return [
			'electronic_address' => compact('scheme', 'value'),
			'identifiers' => compact('scheme', 'value'),
			'legal_registration_identifier' => compact('scheme', 'value'),
			'name' => $person->name,
			'postal_address' => [
				'country_code' => $person->country,
				'address_line1' => $address[0] ?? '',
				'address_line2' => $address[1] ?? '',
				'address_line3' => implode("\n", array_slice($address, 2)),
				'city' => $person->city ?? '',
				'post_code' => $person->post_code ?? '',
			],
			'contact' => [
				'email_address' => $person->email,
				'phone_number' => $person->phone,
			],
			'vat_identifier' => $person->vat_number,
		];
	}
}

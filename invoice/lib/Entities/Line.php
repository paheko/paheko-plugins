<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Plugin\Invoice\Invoices;

use Paheko\DB;
use Paheko\Entity;
use Paheko\Utils;

use DateTime;

class Line extends Entity
{
	const TABLE = 'plugin_invoice_lines';

	protected ?int $id = null;
	protected int $id_invoice;
	protected int $number;
	protected string $label;
	protected ?string $reference;
	protected ?string $description;
	protected string $unit = 'XUN';
	protected float $quantity = 1.0;
	protected int $price;
	protected float $vat_rate = 0.0;
	protected string $vat_code = 'E';
	protected ?string $vat_exemption_code = null;

	/**
	 * @see https://docs.peppol.eu/poacc/billing/3.0/codelist/UNECERec20/
	 */
	const UNITS = [
		'XUN' => 'unité',
		'CMT' => 'centimètre',
		'MTR' => 'mètre',
		'MTK' => 'mètre-carré',
		'MTQ' => 'mètre-cube',
		'KMT' => 'kilomètre',
		'GRM' => 'gramme',
		'KGM' => 'kilogramme',
		'MON' => 'mois',
		'DAY' => 'jour',
		'HUR' => 'heure',
		'MIN' => 'minute',
		'LTR' => 'litre',
		'XZZ' => 'autre',
	];

	const VAT_CODES = [
		'E' => 'Exempté',
		'S' => 'Standard',
		'Z' => 'Exonéré',
	];

	const VAT_RATES = [
		0.2,
		0.1,
		0.055,
		0.021,
		0.0
	];

	public function getVATRatesOptions(): array
	{
		$out = [];

		foreach (self::VAT_RATES as $rate) {
			$out[(string) $rate] = sprintf('%s%%', $rate * 100);
		}

		return $out;
	}

	public function getVATExemptionOptions(): array
	{
		return Invoices::VAT_EXEMPTIONS;
	}

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(mb_strlen(trim($this->label)), 'Le libellé est vide');
		$this->assert(mb_strlen(trim($this->label)) <= 500, 'Le libellé doit faire au maximum 500 caractères');
		$this->assert(!isset($this->description) || mb_strlen(trim($this->description)) <= 10000, 'Le libellé doit faire au maximum 10.000 caractères');
		$this->assert(array_key_exists($this->vat_code, self::VAT_CODES));
		$this->assert(!isset($this->vat_exemption_code) || array_key_exists($this->vat_exemption_code, Invoices::VAT_EXEMPTIONS));
		$this->assert($this->vat_rate >= 0.0);
		$this->assert($this->quantity >= 0.0);

		$this->assert(array_key_exists($this->unit, self::UNITS), 'Unité inconnue : ' . $this->unit);
	}

	public function getVATAmount(): float
	{
		return (float) (($this->getNetTotal() * $this->vat_rate) / 100);
	}

	public function getUnitPrice(): float
	{
		return (float) ($this->price / 100);
	}

	public function getNetTotal(): float
	{
		return (float) ($this->getUnitPrice() * $this->quantity);
	}

	public function getTotal(): float
	{
		return (float) ($this->getNetTotal() + $this->getVATAmount());
	}

	public function filterUserValue(string $type, $value, string $key)
	{
		if ($key == 'price') {
			try {
				$value = abs(Utils::moneyToInteger($value));
			}
			catch (\InvalidArgumentException $e) {
				throw new UserException($e->getMessage(), 0, $e);
			}
		}

		$value = parent::filterUserValue($type, $value, $key);

		return $value;
	}

	public function save(bool $selfcheck = true): bool
	{
		if (!isset($this->number)) {
			$db = DB::getInstance();
			$number = $db->firstColumn('SELECT MAX(number) FROM plugin_invoice_lines WHERE id_invoice = ?;', $this->id_invoice);
			$number++;
			$this->set('number', $number);
		}

		return parent::save($selfcheck);
	}

	/**
	 * Return invoice line as an object ready for EN16931
	 */
	public function exportForInvoice(): array
	{
		$amount = $this->getAmount();
		$vat = $this->price * $this->vat_rate;

		// cf. BR-FR-MAP-08
		$vat_exemption_code = $this->vat_exemption_code === Invoices::ZERO_RATE_VAT_EXEMPTION_CODE ? '' : $this->vat_exemption_code;

		return [
			'identifier'               => (string) $this->id,
			'invoiced_quantity'        => (string) $this->quantity,
			'invoiced_quantity_code'   => $this->unit,
			'item_information'         => [
				'name' => $this->label,
				'description' => $this->description ?? '',
				'seller_identifier' => $this->reference ?? '',
			],
			'net_amount'               => (string) $this->getNetTotal(),
			'price_details'            => ['item_net_price' => (string) $this->getUnitPrice()],
			'vat_information'          => [
				'invoiced_item_vat_category_code' => $this->vat_code,
				'invoiced_item_vat_rate'          => strval($this->vat_rate * 100),
				'exemption_reason_code'           => $vat_exemption_code,
				'exemption_reason'                => Invoices::VAT_EXEMPTIONS[$vat_exemption_code] ?? '',
			],
			'line_vat_amount'          => (string) $this->getVATAmount(),
			'line_with_vat_net_amount' => (string) $this->getTotal(),
		];
	}
}

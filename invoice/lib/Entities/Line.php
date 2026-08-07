<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Plugin\Invoice\Invoices;

use Paheko\DB;
use Paheko\Entity;

use KD2\DB\EntityManager as EM;
use KD2\Office\Money;

use DateTime;
use stdClass;

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
	protected string $quantity = '1';
	protected string $price;
	protected string $vat_rate = '0';
	protected string $vat_code = self::VAT_STANDARD_CODE;

	/**
	 * @see https://docs.peppol.eu/poacc/billing/3.0/codelist/UNECERec20/
	 */
	const UNITS = [
		'XUN' => 'unité',
		'CMT' => 'centimètre',
		'MTR' => 'mètre',
		'MTK' => 'mètre-carré',
		'MTQ' => 'mètre-cube',
		'LM'  => 'mètre-linéaire',
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

	const VAT_EXEMPTION_CODE = 'E';
	const VAT_STANDARD_CODE = 'S';

	/**
	 * S = Taux de TVA standard
	 * Z = Taux de TVA égal à 0 (non applicable en France)
	 * E = Exempté de TVA
	 * AE = Autoliquidation de TVA
	 * K = Autoliquidation pour cause de livraison intracommunautaire
	 * G = Exempté de TVA pour Export hors UE
	 * O = Hors du périmètre d'application de la TVA
	 * L = Iles Canaries
	 * M = Ceuta et Mellila
	 */
	const VAT_CODES = [
		self::VAT_STANDARD_CODE  => 'Standard', // Normal VAT
		self::VAT_EXEMPTION_CODE => 'Exempté', // Exonerated (franchise, non assujetti)
	];

	const VAT_RATES = [
		'0.2',
		'0.1',
		'0.055',
		'0.021',
		'0'
	];

	protected Invoice $_invoice;

	public function invoice(): Invoice
	{
		$this->_invoice ??= EM::findOneById(Invoice::class, $this->id_invoice);
		return $this->_invoice;
	}

	public function getVATRatesOptions(): array
	{
		$out = [];

		foreach (self::VAT_RATES as $rate) {
			$out[(string) $rate] = sprintf('%s%%', $rate * 100);
		}

		if (!$this->invoice()->vat_exemption_code && !$this->invoice()->vat_exemption_text) {
			unset($out['0']);
		}

		return $out;
	}

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(mb_strlen(trim($this->label)), 'Le libellé est vide');
		$this->assert(mb_strlen(trim($this->label)) <= 500, 'Le libellé doit faire au maximum 500 caractères');
		$this->assert(!isset($this->description) || mb_strlen(trim($this->description)) <= 10000, 'Le libellé doit faire au maximum 10.000 caractères');
		$this->assert(array_key_exists($this->vat_code, self::VAT_CODES));

		$this->assert(preg_match('!^\d+(?:\.\d{1,2})?$!', $this->vat_rate), 'Taux de TVA invalide : ' . $this->vat_rate);
		$this->assert(preg_match('!^\d+(?:\.\d{1,10})?$!', $this->quantity), 'Quantité invalide : ' . $this->quantity);
		$this->assert(preg_match('!^\d+(?:\.\d{1,10})?$!', $this->price), 'Prix unitaire invalide : ' . $this->price);

		$this->assert(array_key_exists($this->unit, self::UNITS), 'Unité inconnue : ' . $this->unit);
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		$fields = ['vat_rate', 'quantity', 'price'];

		foreach ($fields as $field) {
			if (isset($source[$field])) {
				$source[$field] = str_replace(',', '.', $source[$field]);
			}
		}

		return parent::importForm($source);
	}

	public function getVATAmount(): string
	{
		return Money::round2(Money::calc($this->getNetTotal(), '*', $this->vat_rate));
	}

	public function getUnitPrice(): string
	{
		return $this->price;
	}

	public function getNetTotal(): string
	{
		return Money::round2(Money::calc($this->getUnitPrice(), '*', $this->quantity));
	}

	public function getTotal(): string
	{
		return Money::round2(Money::calc($this->getNetTotal(), '+', $this->getVATAmount()));
	}

	public function save(bool $selfcheck = true): bool
	{
		if (!$this->vat_rate) {
			$this->set('vat_code', self::VAT_EXEMPTION_CODE);
		}
		else {
			$this->set('vat_code', self::VAT_STANDARD_CODE);
		}

		if (!isset($this->number)) {
			$db = DB::getInstance();
			$number = $db->firstColumn('SELECT MAX(number) FROM plugin_invoice_lines WHERE id_invoice = ?;', $this->id_invoice);
			$number++;
			$this->set('number', $number);
		}

		return parent::save($selfcheck);
	}

	public function saveAndUpdateInvoice(): bool
	{
		$db = DB::getInstance();

		$db->begin();
		$exists = $this->exists();
		$is_total_modified = $this->isModified('price') || $this->isModified('vat_rate') || $this->isModified('quantity');

		$r = $this->save();

		if (!$exists || $is_total_modified) {
			$this->invoice()->updateTotal();
		}

		$db->commit();

		return $r;
	}

	/**
	 * Return invoice line as an object ready for EN16931
	 */
	public function exportForInvoice(): stdClass
	{
		return (object) [
			'identifier'               => (string) $this->id,
			'invoiced_quantity'        => $this->quantity,
			'invoiced_quantity_code'   => $this->unit,
			'item_information'         => (object) [
				'name' => $this->label,
				'description' => $this->description ?? '',
				'seller_identifier' => $this->reference ?? '',
			],
			'net_amount'               => $this->getNetTotal(),
			'price_details'            => (object) ['item_net_price' => $this->getUnitPrice()],
			'vat_information'          => (object) [
				'invoiced_item_vat_category_code' => $this->vat_code,
				'invoiced_item_vat_rate'          => strval($this->vat_rate * 100),
			],
			'line_vat_amount'          => $this->getVATAmount(),
			'line_with_vat_net_amount' => $this->getTotal(),
		];
	}
}

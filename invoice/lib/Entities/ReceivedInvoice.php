<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Config;
use Paheko\DB;
use Paheko\Plugins;
use Paheko\Utils;
use Paheko\Files\Files;
use Paheko\Entities\Files\File;

use KD2\DB\Date;
use KD2\DB\EntityManager as EM;

use stdClass;
use DateTime;

class ReceivedInvoice extends AbstractInvoice
{
	const TABLE = 'plugin_invoice_received';

	protected ?int $id = null;
	protected string $uuid;
	protected int $type;
	protected string $status = self::STATUS_NEW;
	protected string $number;
	protected int $total;
	protected ?int $amount_due = null;
	protected ?string $person_name = null;
	protected ?string $person_id = null;
	protected Date $due_date;
	protected ?Date $issue_date = null;
	protected ?string $provider_name = null;
	protected ?string $provider_id = null;
	/**
	 * EN16931 JSON representation of invoice data
	 */
	protected stdClass $content;
	protected string $format;

	protected ?File $_file;

	const STATUS_NEW = 'new';
	const STATUS_ACCEPTED = 'accepted';
	const STATUS_PARTIAL = 'partial';
	const STATUS_PAID = 'paid';
	const STATUS_REFUSED = 'refused';

	const STATUSES = [
		self::STATUS_NEW => 'Nouveau',
		self::STATUS_ACCEPTED => 'Acceptée',
		self::STATUS_PARTIAL => 'Paiement partiel',
		self::STATUS_PAID => 'Réglée',
		self::STATUS_REFUSED => 'Refusée',
	];

	const STATUSES_COLORS = [
		self::STATUS_NEW => 'orange',
		self::STATUS_ACCEPTED => 'greyblue',
		self::STATUS_PARTIAL => 'red',
		self::STATUS_PAID => 'green',
		self::STATUS_REFUSED => 'tan',
	];

	const FORMAT_JSON = 'superpdp';
	const FORMAT_CII = 'cii';
	const FORMAT_FACTURX = 'facturx';
	const FORMAT_UBL = 'ubl';

	const FORMATS = [
		self::FORMAT_JSON => 'JSON (SuperPDP)',
		self::FORMAT_CII => 'XML (CII)',
		self::FORMAT_FACTURX => 'PDF (Factur-X)',
		self::FORMAT_UBL => 'XML (UBL)',
	];

	const FILES_TYPES = [
		'application/json' => 'json',
		'application/xml'  => 'xml',
		'text/xml'         => 'xml',
		'application/pdf'  => 'pdf',
	];


	public function selfCheck(): void
	{
		parent::selfCheck();
		$db = DB::getInstance();

		$this->assert(strlen($this->number), 'Le numéro est vide');
		$this->assert(array_key_exists($this->format, self::FORMATS), 'Format inconnu');
	}

	public function getTypeLabel(): string
	{
		return self::TYPES[$this->type];
	}

	public function getStatusLabel(): string
	{
		return self::STATUSES[$this->status];
	}

	public function getStatusColor(): string
	{
		return self::STATUSES_COLORS[$this->status];
	}

	public function importForm(?array $source = null)
	{
		throw new \LogicException('Cannot edit a received invoice');
	}

	public function updateTotal(): void
	{
		$this->set('total', Utils::moneyToInteger($this->content->totals->total_with_vat));
		$this->saveOnly(['total']);
	}

	public function delete(): bool
	{
		if ($file = $this->file()) {
			$file->delete();
		}

		return parent::delete();
	}

	public function isQuote(): bool
	{
		return false;
	}

	public function isDraft(): bool
	{
		return false;
	}

	public function isSellerOrg(): bool
	{
		return false;
	}

	public function getExport(): stdClass
	{
		return $this->content;
	}

	public function getReference(): ?string
	{
		return $this->number;
	}

	public function download(): void
	{
		$file = $this->file();
		$file->serve($this->number . '.' . self::FILES_TYPES[$file->mime]);
	}

	public function exportAs(string $format, ?string $parent_format = null): string
	{
		// Factur-X: it is simpler
		if ($format !== 'html' || $parent_format) {
			throw new \LogicException('Cannot export other than HTML');
		}

		return parent::exportAs('html');
	}

	public function getFilePath(): string
	{
		return Plugins::getStorageRoot('invoice') . '/received/' . $this->uuid;
	}

	public function file(): ?File
	{
		$this->_file ??= Files::get($this->getFilePath());
		return $this->_file;
	}

	public function upload(string $key): File
	{
		$this->uuid ??= Utils::uuid();
		$file = Files::upload(Plugins::getStorageRoot('invoice') . '/received', $key, null, $this->uuid);

		if (!array_key_exists($file->mime, self::FILES_TYPES)) {
			$file->delete();
			throw new UserException('Ce fichier n\'est pas un fichier JSON, XML ou PDF valide.');
		}

		$this->_file = $file;

		try {
			$this->importFromFile();
		}
		catch (\RuntimeException $e) {
			$file->delete();
			$this->_file = null;
			throw $e;
		}

		return $this->_file;
	}

	public function importFromJSON(stdClass $data): void
	{
		if (!isset($data->en_invoice, $data->id, $data->company_id, $data->direction)) {
			throw new UserException('Format JSON invalide.');
		}

		if ($data->direction !== 'in') {
			throw new \LogicException('Cannot import emitted invoice as received.');
		}

		$this->set('provider_name', 'superpdp');
		$this->set('person_id', (string) $data->company_id);

		// TODO: import events

		$this->set('format', self::FORMAT_JSON);

		$this->importFromInvoiceJSON($data->en_invoice);
	}

	public function importFromInvoiceJSON(stdClass $data)
	{
		$this->validateInvoiceSchema($data);
		$this->set('content', $data);

		$this->assert(array_key_exists($data->type_code, self::TYPES), 'Type de facture inconnu : ' . $data->type_code);
		$this->set('type', $data->type_code);

		$this->assert(strlen($data->number) <= 100);
		$this->assert(strlen($data->number));
		$this->set('number', $data->number);

		$this->assert(isset($data->totals->total_with_vat));
		$this->set('total', Utils::moneyToInteger($data->totals->total_with_vat));
		$this->set('amount_due', Utils::moneyToInteger($data->totals->amount_due_for_payment));
		$this->set('issue_date', new \DateTime($data->issue_date));
		$this->set('due_date', new \DateTime($data->payment_due_date));

		if ($this->type === self::TYPE_SELF_BILLING) {
			$this->set('person_name', $data->buyer->name);
		}
		else {
			$this->set('person_name', $data->seller->name);
		}
	}

	public function importFromFile(): void
	{
		if ($this->file()->mime === 'application/json') {
			$data = json_decode($this->file()->fetch());

			if (null === $data) {
				throw new UserException('Format JSON corrompu.');
			}

			$this->importFromJSON($data);
			return;
		}

		throw new UserException('Seuls les factures au format JSON (SuperPDP) sont acceptées pour le moment.');

		if ($this->file()->mime === 'application/pdf') {
			$xml = $this->extractXMLFromFacturX($this->file());
		}
		else {
			$xml = $this->file()->fetch();
			// TODO: extract PDF from XML if it is supplied, and store it separately
		}

		$data = $this->parseXML($xml);

		$this->importFromInvoiceJSON($data);
	}

	public function extractXMLFromFacturX(File $file): ?string
	{
		$pdf = $file->fetch();

		if (!$pdf) {
			throw new \RuntimeException('Cannot fetch file contents');
		}

		// 1. Build objects map
		if (!preg_match_all('/(\d+)\s+0\s+obj\b([\s\S]*?)endobj\b/m', $pdf, $matches, PREG_SET_ORDER)) {
			throw new \RuntimeException('Failed to find object map');
		}

		$objects_map = [];
		foreach ($matches as $m) {
			$num  = (int)$m[1];
			$objects_map[$num] = $m[2];
		}

		// 2. Find Filespec object that contains /Type /Filespec
		$found = null;

		foreach ($objects_map as $num => $obj) {
			if (strpos($obj, '/Type /Filespec') !== false) {
				$found = $num;
				break;
			}
		}

		if ($found === null) {
			throw new \RuntimeException('Failed to find Filespec object');
		}

		$object = $objects_map[$found];

		// Clean-up memory
		unset($found, $pdf);

		// 3. Find EmbeddedFile object referenced in /EF << /F X 0 R >>
		if (!preg_match('/\/EF\s*<<[\s\S]*?\/F\s+(\d+)\s+0\s+R/', $object, $m)) {
			throw new \RuntimeException('Failed to find EmbeddedFile object');
		}

		$xml_obj_num = (int)$m[1];

		if (!isset($objects_map[$xml_obj_num])) {
			return null;
		}

		$xml_obj = $objects_map[$xml_obj_num];

		// 4. Extract stream
		if (!preg_match('/stream[\r\n]+([\s\S]*?)endstream/', $xml_obj, $m)) {
			throw new \RuntimeException('Failed to find PDF stream');
		}

		$raw = $m[1];

		// 5. Uncompressed object FlateDecode, if needed
		if (0 !== strpos($raw, '<?xml')) {
			$xml = @gzuncompress($raw);

			if ($xml === false) {
				$xml = @gzinflate($raw);
			}

			if ($xml === false) {
				throw new \RuntimeException('Failed to decompress PDF object');
			}
		}

		return $xml;
	}

	public function getPerson(): stdClass
	{
		if ($this->type === self::TYPE_SELF_BILLING) {
			return $this->content->buyer;
		}
		else {
			return $this->content->seller;
		}
	}
}

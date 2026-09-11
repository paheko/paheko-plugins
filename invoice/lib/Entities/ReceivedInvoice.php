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
use KD2\Office\Money;

use stdClass;
use DateTime;

class ReceivedInvoice extends AbstractInvoice
{
	const TABLE = 'plugin_invoice_received';

	protected ?int $id = null;
	protected string $uuid;
	protected int $type;
	protected string $status;
	protected string $number;
	protected int $total;
	protected Date $date;
	protected ?Date $date_expiry = null;
	protected ?string $provider_name = null;
	protected ?string $provider_id = null;
	/**
	 * EN16931 JSON representation of invoice data
	 */
	protected stdClass $content;
	protected string $format;

	protected ?File $_file;

	const STATUS_UNREAD = 'unread';
	const STATUS_READ = 'read';
	const STATUS_FLAGGED = 'flagged';
	const STATUS_DONE = 'done';

	const STATUSES = [
		self::STATUS_UNREAD => 'Non lue',
		self::STATUS_READ => 'Lue',
		self::STATUS_DONE => 'Réglée',
	];

	const STATUSES_COLORS = [
		self::STATUS_UNREAD => 'orange',
		self::STATUS_READ => 'greyblue',
		self::STATUS_DONE => 'green',
	];

	const FORMAT_CII = 'cii';
	const FORMAT_FACTURX = 'facturx';
	const FORMAT_UBL = 'ubl';

	const FORMATS = [
		self::FORMAT_CII => 'XML (CII)',
		self::FORMAT_FACTURX => 'PDF (Factur-X)',
		self::FORMAT_UBL => 'XML (UBL)',
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
		$this->file()->serve(true);
	}

	public function exportAs(string $format, ?string $parent_format = null): string
	{
		// Factur-X: it is simpler
		if ($format !== 'html' || $parent_format) {
			throw new \LogicException('Cannot export other than HTML');
		}

		parent::exportAs('html');
	}

	public function getFilePath(): string
	{
		return Plugins::getStorageRoot('invoice') . '/received/' . $this->uuid;
	}

	public function file(): ?File
	{
		$this->file ??= Files::get($this->getFilePath());
		return $this->file;
	}

	public function upload(string $key): File
	{
		$file = Files::upload(Plugins::getStorageRoot('invoice') . '/received', $key, null, $this->uuid);

		if (!in_array($file->mime, ['application/xml', 'text/xml', 'application/pdf'], true)) {
			$file->delete();
			throw new UserException('Ce fichier n\'est pas un fichier XML ou PDF valide.');
		}

		$this->_file = $file;

		try {
			$this->importDataFromFile();
		}
		catch (\RuntimeException $e) {
			$file->delete();
			$this->_file = null;
			throw $e;
		}

		return $this->_file;
	}

	public function importDataFromFile(): void
	{
		if ($this->file()->mime === 'application/pdf') {
			$xml = $this->extractXMLFromFacturX();
		}
		else {
			$xml = $this->file()->fetch();
		}

		// TODO: extract PDF from XML if it is supplied, and store it
	}

	public function extractXMLFromFacturX(): ?string
	{
		$pdf = $this->file()->fetch();

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
}

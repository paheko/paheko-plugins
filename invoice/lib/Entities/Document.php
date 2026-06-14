<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Config;
use Paheko\Entity;
use Paheko\Utils;

use KD2\DB\Date;
use stdClass;

class Document extends Entity
{
	const TABLE = 'plugin_invoice_documents';

	protected ?int $id = null;
	protected int $id_client;
	protected ?int $id_transaction = null;
	protected ?int $id_quote = null;
	protected ?int $number = null;
	protected int $type;
	protected string $label;
	protected Date $date_created;
	protected ?Date $date_expiry = null;
	protected ?Date $date_sent = null;
	protected string $status = 'draft';
	protected int $total = 0;
	protected ?string $notes = null;

	/**
	 * Buyer reference (Factur-X: code du service exécutant)
	 */
	protected ?string $buyer_ref = null;

	/**
	 * Factur-X : Numéro d'engagement (IssuerAssignedID)
	 */
	protected ?string $contract_reference = null;
	protected ?stdClass $content = null;

	const TYPE_QUOTE = 231;

	/**
	 * @see https://service.unece.org/trade/untdid/d99a/uncl/uncl1001.htm
	 * @see https://api.agicap.com/guides/einvoicing
	 */
	const TYPES = [
		self::TYPE_QUOTE => 'Devis',
		380 => 'Facture',
		//386 => 'Facture d\'acompte',
		//381 => 'Avoir',
	];

	const STATUS_DRAFT = 'draft';
	const STATUS_AWAITING_VALIDATION = 'awaiting_validation';
	const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
	const STATUS_PAID = 'paid';
	const STATUS_CANCELLED = 'cancelled';

	const STATUSES = [
		self::STATUS_DRAFT => 'Brouillon',
		self::STATUS_AWAITING_VALIDATION => 'En attente de validation',
		self::STATUS_CREATED => 'Créée',
		self::STATUS_SENT => 'Envoyée',
		self::STATUS_PAID => 'Payée',
		self::STATUS_CANCELLED => 'Annulé',
	];

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(mb_strlen(trim($this->label)), 'Le libellé est vide');
		$this->assert(mb_strlen(trim($this->label)) <= 500, 'Le libellé doit faire au maximum 500 caractères');
		$this->assert(!isset($this->description) || mb_strlen(trim($this->description)) <= 10000, 'La description doit faire au maximum 10.000 caractères');

		if ($this->number) {
			$this->assert(!$db->test(self::TABLE, 'id != ? AND number = ?', $this->number));
		}
	}

	public function canSend(): bool
	{
		if ($this->type === self::TYPE_QUOTE) {
			return false;
		}

		if ($this->status !== self::STATUS_CREATED) {
			return false;
		}

		return true;
	}

	public function publish(): void
	{
		$this->set('date_created', new Date);
		$where_type = $this->type === self::TYPE_QUOTE ? 'type = ?' : 'type != ?';
		$new_number = $db->firstColumn('SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE ' . $where_type . ' AND status != ?;', self::TYPE_QUOTE, self::STATUS_DRAFT);
		$new_number++;
		$this->set('number', sprintf('%s-%d-%d', $this->type === self::TYPE_QUOTE ? 'D' : 'F', $this->date_created->format('Y'), $new_number));
	}

	/**
	 * Return invoice line as an object ready for EN16931
	 */
	public function exportForInvoice(): array
	{
		$config = Config::getInstance();
		$price = Utils::money_format($this->price, '.', '', true);

		$seller_address = explode("\n", $config->org_address);

		if (strlen($config->currency) !== mb_strlen($config->currency)
			|| strlen($config->currency) !== 3) {
			throw new UserException('La devise sélectionnée est invalide, merci de la modifier dans la configuration.');
		}

		$out = [
			'buyer' => $this->client()->exportForInvoice(),
			'seller' => Clients::exportOrgForInvoice(),
			'currency_code' => $config->currency,
			'type_code' => $this->type,
			'issue_date' => $this->date_created->format('Y-m-d'),
			'payment_due_date' => $this->date_expiry ? $this->date_expiry->format('Y-m-d') : null,
			'lines' => [],
			'number' => $this->number ?? 'Brouillon',
			'process_control' => [
				'specification_identifier' => 'urn:cen.eu:en16931:2017',
				'business_process_type' => 'M1', // Mixed invoice (goods and services that are not ancillary to each other)
			],
			// Référence acheteur. "Service exécutant" Code service pour Chorus Pro. Obligatoire pour les entités publiques marquées « Service obligatoire » dans Chorus Pro.
			'buyer_reference' => $this->buyer_ref ?? '',
			// Numéro commande acheteur. "Numéro d'engagement juridique" Texte libre. Pour Chorus Pro, indiquer ici le numéro d'engagement. Obligatoire pour les entités publiques marquées « Engagement obligatoire » dans Chorus Pro.
			'contract_reference' => $this->contract_reference ?? '',
			'notes' => [['note' => $this->notes ?? '']],
			//'payment_terms' =>
		];

		$vat = [];
		$lines = [];
		$vat_total = 0.0;
		$net_total = 0.0;

		foreach ($this->listLines() as $line) {
			$lines[] = $line->exportForInvoice();

			$vat_total += $line->getVATAmount();
			$net_total += $line->getNetTotal();

			// Add VAT breakdown information, it has to be different for each exemption reason
			$vat_code = $line->vat_code . ($line->vat_exemption_code ?? '');
			$vat[$vat_code] ??= [
				'vat_category_code'           => $line->vat_code,
				'vat_category_tax_amount'     => 0,
				'vat_category_taxable_amount' => 0,
				'vat_exemption_reason_code'   => $line->vat_exemption_code,
				'vat_exemption_reason'        => Invoices::VAT_EXEMPTIONS[$line->vat_exemption_code],
			];

			$vat[$vat_code]['vat_category_tax_amount'] += $line->getVATAmount();
			$vat[$vat_code]['vat_category_taxable_amount'] += $line->getNetTotal();
		}

		$paid = 0; // TODO

		$out['totals'] = [
			'amount_due_for_payment'   => (string) (($net_total + $vat_total) - $paid),
			'sum_invoice_lines_amount' => (string) $net_total,
			'total_with_vat'           => (string) ($net_total + $vat_total),
			'total_without_vat'        => (string) $net_total,
			'paid_amount'              => (string) $paid,
			'total_vat_amount'         => (string) $vat_total,
		];

		$out['vat_break_down'] = [];

		foreach ($vat as $item) {
			$item['vat_category_tax_amount'] = (string) $item['vat_category_tax_amount'];
			$item['vat_category_taxable_amount'] = (string) $item['vat_category_taxable_amount'];
			$out['vat_break_down'][] = $item;
		}

		return $out;
	}

	public function exportAs(string $format): string
	{
		if ($format !== 'html') {
			if ($this->type === self::TYPE_QUOTE) {
				throw new UserException('Il n\'est pas possible d\'exporter un devis');
			}
			elseif ($this->status === self::STATUS_DRAFT) {
				throw new UserException('Il n\'est pas possible d\'exporter un document en brouillon');
			}
		}

		if ($format === 'facturx') {
			$xml = $this->exportAs('cii');
			$html = $this->exportAs('html');
			return $this->createFacturX($xml, $html);
		}

		$template = match ($format) {
			'cii' => 'cii.xml',
			'ubl' => 'ubl.xml',
			'html' => 'invoice.html',
		};

		$tpl->assign('invoice', $this->content ?? $this->exportForInvoice());
		$out = $tpl->fetch(PLUGIN_ROOT . '/templates/invoice/' . $template);

		if ($format === 'facturx') {
			$out = $this->createFacturX($out);
		}

		return $out;
	}

	public function streamAs(string $format, bool $download = false): void
	{
		$mimetype = match ($format) {
			'facturx' => 'application/pdf',
			'html'    => 'text/html',
			default   => 'text/xml',
		};

		$extension = match($format) {
			'facturx' => 'pdf',
			'html'    => 'html',
			default   => 'xml',
		};

		if ($this->status === self::STATUS_DRAFT && $format !== 'html') {
			throw new \LogicException('Cannot download a draft');
		}

		header('Content-Type: ' . $mimetype);

		if ($this->number) {
			header(sprintf('Content-Disposition: %s; filename="%s"', $download ? 'attachment' : 'inline', $this->number . '.' . $extension));
		}

		echo $this->exportAs($format);
	}

	/**
	 * @see https://www.ghostscript.com/blog/zugferd.html
	 * TODO: reference here: https://fnfe-mpe.org/factur-x/qui-propose-factur-x/
	 */
	protected function createFacturX(string $xml, string $html): string
	{
		$signal = Plugins::fire('facturx.create', ['html' => $html, 'xml' => $xml], ['pdf_string' => null]);

		if ($signal) {
			if ($str = $signal->getOut('pdf_string')) {
				return $str;
			}
			else {
				throw new \LogicException('Signal facturx.create did not return a string');
			}
		}

		$id = 'facturx_' . sha1(random_bytes(10));
		Static_Cache::store($id, $xml);
		$tmp_xml_file = Static_Cache::getPath($id);

		// Prince can directly create a valid Factur-X PDF using STDIN/STDOUT,
		// without temporary files for HTML and PDF, much better
		if (Utils::getPDFCommand() === 'prince') {
			$cmd = sprintf(
				'prince --http-timeout=3 --pdf-profile="PDF/A-3a" --pdf-xmp=%s --attach-data=%s -o %s -',
				escapeshellarg(PLUGIN_ROOT . '/facturx.xmp'),
				escapeshellarg($tmp_xml_file),
				escapeshellarg($path ?? '-')
			);

			$out = '';
			Utils::exec($cmd, 10, $html, fn ($data) => $out .= $data);
			return $out;
		}

		if (!Utils::quick_exec('which gs', 1)) {
			throw new \LogicException('Cannot create Factur-X file: ghostscript is not installed');
		}

		// If Prince is not available, use ghostscript
		$tmp_pdf_file = Utils::filePDF($html);

		$cmd = sprintf('gs --permit-file-read=%s'
			. ' -sDEVICE=pdfwrite'
			. ' -dPDFA=3'
			. ' -sColorConversionStrategy=RGB'
			. ' -sZUGFeRDXMLFile=%s'
			. ' -sZUGFeRDProfile=%s'
			. ' -sZUGFeRDVersion=2p1'
			. ' -sZUGFeRDConformanceLevel=MINIMUM'
			. ' -dPDFACompatibilityPolicy=1'
			. ' -o %s %s %s',
			escapeshellarg(PLUGIN_ROOT . ':' . STATIC_CACHE_ROOT),
			escapeshellarg($tmp_xml_file),
			escapeshellarg(PLUGIN_ROOT . '/factur-x/rgb.icc'),
			escapeshellarg($path ?? '-'),
			escapeshellarg(PLUGIN_ROOT . '/factur-x/zugferd.ps'),
			escapeshellarg($tmp_pdf_file)
		);

		try {
			return Utils::quick_exec($cmd, 5);
		}
		finally {
			Static_Cache::remove($id);
			Utils::safe_unlink($tmp_pdf_file);
		}
	}

	public function canExportAsFacturX(): bool
	{
		if (Plugins::hasSignal('facturx.create')) {
			return true;
		}

		if (Utils::getPDFCommand() === 'prince') {
			return true;
		}

		return (bool) Utils::quick_exec('which gs', 1);
	}
}

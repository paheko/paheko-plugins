<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Config;
use Paheko\DynamicList;
use Paheko\Entity;
use Paheko\Plugins;
use Paheko\Static_Cache;
use Paheko\Template;
use Paheko\UserException;
use Paheko\Utils;

use KD2\DB\Date;
use KD2\DB\EntityManager as EM;
use KD2\Office\Money;

use Generator;
use stdClass;

use Paheko\Plugin\Invoice\Clients;
use Paheko\Plugin\Invoice\Invoices;

use const Paheko\STATIC_CACHE_ROOT;

class Invoice extends Entity
{
	const TABLE = 'plugin_invoice_invoices';

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

	/**
	 * France: type d'opération
	 * "l'information selon laquelle les opérations donnant lieu à une facture
	 * sont constituées exclusivement de livraisons de biens ou de prestations
	 * de services ou sont constituées de ces deux catégories d'opérations"
	 */
	protected ?string $operation_type = null;

	protected ?stdClass $content = null;

	protected ?string $provider_name = null;
	protected ?string $provider_id = null;

	protected Client $_client;

	const TYPE_QUOTE = 231;
	const TYPE_INVOICE = 380;

	/**
	 * @see https://service.unece.org/trade/untdid/d99a/uncl/uncl1001.htm
	 * @see https://api.agicap.com/guides/einvoicing
	 */
	const TYPES = [
		self::TYPE_QUOTE => 'Devis',
		self::TYPE_INVOICE => 'Facture',
		//386 => 'Facture d\'acompte',
		//381 => 'Avoir',
	];

	const TYPES_PLURAL = [
		self::TYPE_QUOTE => 'Devis',
		self::TYPE_INVOICE => 'Factures',
	];

	const OPERATION_TYPES = [
		'mixed'    => 'Livraisons de biens et prestations de services',
		'goods'    => 'Livraisons de biens',
		'services' => 'Prestations de service',
	];

	/**
	 * Quote state life: draft, awaiting_send, awaiting_validation, then 'accepted' or 'cancelled'
	 * Invoice: draft, awaiting_send, awaiting_payment, paid
	 */
	const STATUS_DRAFT = 'draft';
	const STATUS_AWAITING_SEND = 'awaiting_send';
	const STATUS_AWAITING_VALIDATION = 'awaiting_validation';
	const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
	const STATUS_PAID = 'paid';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_ACCEPTED = 'accepted';

	const STATUSES = [
		self::STATUS_DRAFT => 'Brouillon',
		self::STATUS_AWAITING_SEND => 'À envoyer',
		self::STATUS_AWAITING_VALIDATION => 'À valider',
		self::STATUS_AWAITING_PAYMENT => 'À payer',
		self::STATUS_PAID => 'Payée',
		self::STATUS_CANCELLED => 'Annulé',
		self::STATUS_ACCEPTED => 'Accepté',
	];

	const STATUSES_COLORS = [
		self::STATUS_DRAFT => 'darkgray',
		self::STATUS_AWAITING_SEND => 'purple',
		self::STATUS_AWAITING_VALIDATION => 'darkorange',
		self::STATUS_AWAITING_PAYMENT => 'darkred',
		self::STATUS_PAID => 'darkgreen',
		self::STATUS_CANCELLED => 'darkgray',
		self::STATUS_ACCEPTED => 'darkgreen',
	];

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(mb_strlen(trim($this->label)), 'Le libellé est vide');
		$this->assert(mb_strlen(trim($this->label)) <= 500, 'Le libellé doit faire au maximum 500 caractères');
		$this->assert(!isset($this->description) || mb_strlen(trim($this->description)) <= 10000, 'La description doit faire au maximum 10.000 caractères');

		if ($this->isQuote()) {
			$where_type = ' AND type = ' . self::TYPE_QUOTE;
		}
		else {
			$where_type = ' AND type != ' . self::TYPE_QUOTE;
		}

		if ($this->number && $this->exists()) {
			$this->assert(!$db->test(self::TABLE, 'id != ? AND number = ?' . $where_type, $this->id(), $this->number));
		}
		elseif ($this->number) {
			$this->assert(!$db->test(self::TABLE, 'number = ?' . $where_type, $this->number));
		}
	}

	public function isQuote(): bool
	{
		return $this->type === self::TYPE_QUOTE;
	}

	public function isDraft(): bool
	{
		return $this->status === self::STATUS_DRAFT;
	}

	public function canEdit(): bool
	{
		return $this->status === self::STATUS_DRAFT;
	}

	public function canPay(): bool
	{
		return !$this->isQuote() && $this->status === self::STATUS_AWAITING_PAYMENT;
	}

	public function requiresSendingToProvider(): bool
	{
		if ($this->isQuote()) {
			return false;
		}

		if ($this->status === self::STATUS_AWAITING_SEND) {
			return true;
		}

		return false;
	}

	public function getOperationTypeLabel(): ?string
	{
		return self::OPERATION_TYPES[$this->operation_type ?? ''] ?? null;
	}

	public function getStatusLabel(): string
	{
		return self::STATUSES[$this->status];
	}

	public function getStatusColor(): string
	{
		return self::STATUSES_COLORS[$this->status];
	}

	public function client(): Client
	{
		$this->_client ??= Clients::get($this->id_client);
		return $this->_client;
	}

	public function getClientSelectorValue(): ?array
	{
		if (!isset($this->id_client)) {
			return null;
		}

		return [$this->id_client => $this->client()->name];
	}

	public function validate(int $number = 1): void
	{
		if (!$this->isDraft()) {
			throw new \LogicException('Cannot publish a non-draft');
		}

		if (!$this->isQuote()) {
			$config = Config::getInstance();
			$this->assert(!empty($config->org_address), 'L\'adresse de votre organisation n\'est pas renseignée.');
		}

		$where_type = $this->type === self::TYPE_QUOTE ? 'type = ?' : 'type != ?';
		$new_number = $db->firstColumn('SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE ' . $where_type . ' AND status != ?;', self::TYPE_QUOTE, self::STATUS_DRAFT);

		if ($new_number) {
			$new_number++;
		}
		else {
			$new_number = $number;
		}

		$this->set('number', sprintf('%s-%d-%d', $this->type === self::TYPE_QUOTE ? 'D' : 'F', $this->date_created->format('Y'), $new_number));
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['client']) && is_array($source['client'])) {
			$source['id_client'] = (int) key($source['client']);
		}

		// Some values cannot be set by the user
		unset($source['type'], $source['status'], $source['client'], $source['id_quote'],
			$source['number'], $source['total'], $source['content'],
			$source['provider_name'], $source['provider_id']);

		return parent::importForm($source);
	}

	public function updateTotal(): void
	{
		$content = $this->content ?? $this->exportForInvoice();
		$this->set('total', Utils::moneyToInteger($content['totals']['total_with_vat']));
		$this->saveOnly(['total']);
	}

	public function getExport(): array
	{
		return $this->content ?? $this->exportForInvoice();
	}

	/**
	 * Return invoice line as an object ready for EN16931
	 */
	public function exportForInvoice(): array
	{
		$config = Config::getInstance();

		$is_seller_eu = in_array($config->country, Client::EU_COUNTRIES);

		$seller_address = explode("\n", $config->org_address);
		$config->currency = 'EUR'; //FIXME

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
			'notes' => [
				[
					'subject_code' => 'AAI',
					'note' => $this->label,
				],
			],
			//'payment_terms' => // FIXME
		];

		if ($this->notes) {
			$out['notes'][] = [
				'subject_code' => 'OSI',
				'note' => $this->notes,
			];
		}

		// Add operation type (mandatory in France since 2026)
		if ($this->operation_type) {
			$out['notes'][] = [
				'subject_code' => 'REG',
				'note' => $this->getOperationTypeLabel(),
			];
		}

		// Add mandatory mention of recovery costs
		// see https://www.economie.gouv.fr/entreprises/gerer-son-entreprise-au-quotidien/gerer-sa-comptabilite-et-ses-demarches/mentions-obligatoires-dune-facture-tout-savoir
		if ($this->client()->isBusiness()
			&& $is_seller_eu) {
			$out['notes'][] = [
				'subject_code' => 'PMT',
				'note' => 'En cas de retard de paiement, indemnité forfaitaire légale pour frais de recouvrement de 40 euros.',
			];
		}

		$vat = [];
		$vat_total = '0';
		$net_total = '0';

		foreach ($this->iterateLines() as $line) {
			$e = $line->exportForInvoice();
			$out['lines'][] = $e;
			$e = (object) $e;
			$e->vat_information = (object) $e->vat_information;

			$vat_total = Money::calc($vat_total, '+', $e->line_vat_amount);
			$net_total = Money::calc($net_total, '+', $e->net_amount);

			// Add VAT breakdown information, it has to be different for each exemption reason
			$vat_code = md5($e->vat_information->invoiced_item_vat_category_code
				. ($e->vat_information->exemption_reason_code ?? '')
				. $e->vat_information->invoiced_item_vat_rate);
			$vat[$vat_code] ??= [
				'vat_category_code'           => $e->vat_information->invoiced_item_vat_category_code,
				'vat_category_tax_amount'     => '0', // Will be filled below
				'vat_category_taxable_amount' => '0', // Will be filled below
				'vat_exemption_reason_code'   => $e->vat_information->exemption_reason_code,
				'vat_exemption_reason'        => $e->vat_information->exemption_reason,
				'vat_category_rate'           => $e->vat_information->invoiced_item_vat_rate,
			];

			$vat[$vat_code]['vat_category_tax_amount'] = Money::calc($vat[$vat_code]['vat_category_tax_amount'], '+', $e->line_vat_amount);
			$vat[$vat_code]['vat_category_taxable_amount'] = Money::calc($vat[$vat_code]['vat_category_taxable_amount'], '+', $e->net_amount);
		}

		$paid = '0.00'; // TODO

		$out['totals'] = [
			'amount_due_for_payment'   => Money::calc(Money::calc($net_total, '+', $vat_total), '-', $paid),
			'sum_invoice_lines_amount' => $net_total,
			'total_with_vat'           => Money::calc($net_total, '+', $vat_total),
			'total_without_vat'        => $net_total,
			'paid_amount'              => $paid,
			'total_vat_amount'         => $vat_total,
		];

		$out['vat_break_down'] = array_values($vat);

		return $out;
	}

	public function exportAs(string $format): string
	{
		if ($format !== 'html') {
			if ($this->type === self::TYPE_QUOTE) {
				throw new UserException('Il n\'est pas possible d\'exporter un devis');
			}
			elseif ($this->status === self::STATUS_DRAFT) {
				//throw new UserException('Il n\'est pas possible d\'exporter un document en brouillon');
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
			'html' => 'print.html',
		};

		$tpl = Template::getInstance();

		if ($format === 'html') {
			$tpl->assign('is_org', true);
			$tpl->assign('status', $this->status);
			$tpl->assign('is_quote', $this->isQuote());

			if (isset($_GET['print'])) {
				$tpl->assign('facturx_enabled', $this->canExportAsFacturX());
			}
			else {
				$tpl->assign('export', true);
			}
		}

		$tpl->assign('invoice', $this->getExport());

		if ($format === 'cii') {
			$tpl->setEscapeType('xml');
		}

		return $tpl->fetch(__DIR__ . '/../../templates/invoice/' . $template);
	}

	public function streamAs(string $format, bool $download = false): void
	{
		$mimetype = match ($format) {
			'facturx' => 'application/pdf',
			'html'    => 'text/html',
			default   => 'text/xml',
		};

		if ($this->status === self::STATUS_DRAFT && $format !== 'html') {
			//throw new \LogicException('Cannot download a draft');
		}

		header('Content-Type: ' . $mimetype);

		header(sprintf('Content-Disposition: %s; filename="%s"', $download ? 'attachment' : 'inline', $this->getFilename($format)));

		echo $this->exportAs($format);
	}

	public function downloadAs(string $format): void
	{
		$this->streamAs($format, true);
	}

	public function getFilename(string $format): string
	{
		$extension = match($format) {
			'facturx' => 'pdf',
			'html'    => 'html',
			default   => 'xml',
		};

		return ($this->number ?? 'Brouillon') . '.' . $extension;
	}

	/**
	 * @see https://www.ghostscript.com/blog/zugferd.html
	 * TODO: reference here: https://fnfe-mpe.org/factur-x/qui-propose-factur-x/
	 */
	protected function createFacturX(string $xml, string $html): string
	{
		$signal = Plugins::fire('facturx.create', true, ['html' => $html, 'xml' => $xml], ['pdf_string' => null]);

		if ($signal) {
			if ($str = $signal->getOut('pdf_string')) {
				return $str;
			}
			else {
				throw new \LogicException('Signal facturx.create did not return a string');
			}
		}

		$id = 'facturx_' . sha1(random_bytes(10));
		$tmp_xml_dir = STATIC_CACHE_ROOT . '/' . $id;
		$tmp_xml_file = $tmp_xml_dir . '/factur-x.xml';
		$root = realpath(__DIR__ . '/../..');

		// We can't use Static_Cache class as the file MUST be called "factur-x.xml"
		// or it won't work!
		mkdir($tmp_xml_dir);

		file_put_contents($tmp_xml_file, $xml);

		$cmd = null;

		// Prince can directly create a valid Factur-X PDF using STDIN/STDOUT,
		// without temporary files for HTML and PDF, much better
		if (Utils::getPDFCommand() === 'prince') {
			$cmd = sprintf(
				'prince --http-timeout=3 --pdf-profile="PDF/A-3a" --pdf-xmp=%s --attach-data=%s -o - -',
				escapeshellarg($root . '/factur-x/factur-x.xmp'),
				escapeshellarg($tmp_xml_file)
			);
		}
		// Weasyprint can also do it: https://github.com/Kozea/WeasyPrint/pull/2658
		elseif (Utils::getPDFCommand() === 'weasyprint') {
			$cmd = sprintf('weasyprint - - --attachment=%s --attachment-relationship=Data --xmp-metadata=%s --pdf-variant=pdf/a-3a',
				escapeshellarg($tmp_xml_file),
				escapeshellarg($root . '/factur-x/factur-x.xmp')
			);
		}

		try {
			if (null !== $cmd) {
				$out = '';
				// using function is mandatory, fn($data) => $out.= $data doesn't work!
				Utils::exec($cmd, 10, $html, function($data) use (&$out) { $out .= $data; });
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
				escapeshellarg($root . ':' . STATIC_CACHE_ROOT),
				escapeshellarg($tmp_xml_file),
				escapeshellarg($root . '/factur-x/rgb.icc'),
				escapeshellarg($path ?? '-'),
				escapeshellarg($root . '/factur-x/zugferd.ps'),
				escapeshellarg($tmp_pdf_file)
			);

			return Utils::quick_exec($cmd, 5);
		}
		finally {
			if (isset($tmp_pdf_file)) {
				Utils::safe_unlink($tmp_pdf_file);
			}

			Utils::safe_unlink($tmp_xml_file);
			@rmdir($tmp_xml_dir);
		}
	}

	public function canExportAsFacturX(): bool
	{
		if (Plugins::hasSignal('facturx.create')) {
			return true;
		}

		if (in_array(Utils::getPDFCommand(), ['prince', 'weasyprint'], true)) {
			return true;
		}

		return (bool) Utils::quick_exec('which gs', 1);
	}

	public function getPaymentsList(): DynamicList
	{
		$columns = [
			'id' => [
				'label' => 'Numéro',
				'select' => 't.id',
			],
			'label' => [
				'label' => 'Libellé',
				'select' => 't.label',
			],
			'date' => [
				'label' => 'Date',
				'select' => 't.date',
			],
			'amount' => [
				'label' => 'Montant',
				'select' => 'SUM(l.credit)',
			],
		];

		$tables = 'acc_transactions t
			INNER JOIN plugin_invoice_payments p ON p.id_transaction = t.id
			INNER JOIN acc_transactions_lines l ON l.id_transaction = t.id';


		$list = new DynamicList($columns, $tables, 'p.id_invoice = ' . (int)$this->id());
		$list->orderBy('date', false);
		$list->groupBy('t.id');

		return $list;
	}

	public function getLine(int $id): ?Line
	{
		return EM::findOne(Line::class, 'SELECT * FROM @TABLE WHERE id = ? AND id_invoice = ?;', $id, $this->id());
	}

	public function iterateLines(): Generator
	{
		return EM::getInstance(Line::class)->iterate('SELECT * FROM @TABLE WHERE id_invoice = ? ORDER BY number;', $this->id());
	}
}

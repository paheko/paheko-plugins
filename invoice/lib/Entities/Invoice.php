<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Config;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Email\Emails;
use Paheko\Entity;
use Paheko\Exec;
use Paheko\Plugins;
use Paheko\Static_Cache;
use Paheko\Template;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Files\Files;
use Paheko\Entities\Files\File;

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
	protected ?int $id_invoice = null;
	protected int $type;
	protected ?int $year = null;
	protected ?int $number = null;
	protected string $label;
	protected Date $date_created;
	protected ?Date $date_expiry = null;
	protected ?Date $date_sent = null;
	protected string $status = 'draft';
	protected int $total = 0;
	protected ?string $notes = null;

	/**
	 * Currently there might only be one VAT exemption reason per invoice
	 * You need to create multiple invoices if you have multiple exemption reasons
	 */
	protected ?string $vat_exemption_code = null;

	/**
	 * Only France has specific exemption codes right now, others should use the text I guess
	 * @see https://efacture.belgium.be/fr/FAQ/questions-specifiques-sur-la-facturation-electronique
	 */
	protected ?string $vat_exemption_text = null;

	/**
	 * BT-10
	 * Buyer reference (Factur-X: code du service exécutant)
	 */
	protected ?string $buyer_ref = null;

	/**
	 * BT-13
	 * Factur-X/Chorus Pro : Numéro d'engagement (IssuerAssignedID)
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
	protected ?Invoice $_invoice = null;

	const TYPE_QUOTE = 231;
	const TYPE_INVOICE = 380;
	const TYPE_CREDIT = 381;
	//const TYPE_CORRECTION = 384;
	const TYPE_SELF_BILLING = 389;

	/**
	 * Factur-X (BT-3) only allows some codes, not all of them!
	 * @see https://service.unece.org/trade/untdid/d99a/uncl/uncl1001.htm
	 * @see https://api.agicap.com/guides/einvoicing
	 */
	const TYPES = [
		self::TYPE_QUOTE => 'Devis',
		self::TYPE_INVOICE => 'Facture',
		self::TYPE_CREDIT => 'Avoir', // Avoir : quand la facture d'origine a déjà été payée
		self::TYPE_SELF_BILLING => 'Auto-facturation',
		//self::TYPE_CORRECTION => 'Facture rectificative', // rectificative : quand la facture d'origine n'a pas été payée ET qu'on ne modifie aucun montant
		//386 => 'Facture d\'acompte',
	];

	const TYPES_PREFIXES = [
		self::TYPE_QUOTE   => 'DEV',
		self::TYPE_INVOICE => 'FAC',
		self::TYPE_SELF_BILLING => 'FAC',
		self::TYPE_CREDIT  => 'AV',
	];

	const TYPES_PLURAL = [
		self::TYPE_QUOTE => 'Devis',
		self::TYPE_INVOICE => 'Factures',
		self::TYPE_CREDIT => 'Avoirs',
	];

	const OPERATION_TYPES = [
		'M1' => 'Livraisons de biens et prestations de services',
		'B1' => 'Livraisons de biens',
		'S1' => 'Prestations de service',
	];

	/**
	 * Quote state life: draft, awaiting_send, awaiting_validation, then 'accepted' or 'cancelled'
	 * Invoice: draft, awaiting_send, awaiting_payment, paid
	 */
	const STATUS_DRAFT = 'draft';
	const STATUS_AWAITING_SEND = 'awaiting_send';
	const STATUS_AWAITING_VALIDATION = 'awaiting_validation';
	const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
	const STATUS_AWAITING_REFUND = 'awaiting_refund';
	const STATUS_PAID = 'paid';
	const STATUS_REFUNDED = 'refunded';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_ACCEPTED = 'accepted';

	const STATUSES = [
		self::STATUS_DRAFT => 'Brouillon',
		self::STATUS_AWAITING_SEND => 'À envoyer',
		self::STATUS_AWAITING_VALIDATION => 'À valider',
		self::STATUS_AWAITING_PAYMENT => 'À payer',
		self::STATUS_AWAITING_REFUND => 'Remboursement en attente',
		self::STATUS_PAID => 'Payé',
		self::STATUS_REFUNDED => 'Remboursé',
		self::STATUS_CANCELLED => 'Annulé',
		self::STATUS_ACCEPTED => 'Accepté',
	];

	const STATUSES_COLORS = [
		self::STATUS_DRAFT => 'darkgray',
		self::STATUS_AWAITING_SEND => 'purple',
		self::STATUS_AWAITING_VALIDATION => 'darkorange',
		self::STATUS_AWAITING_PAYMENT => 'darkred',
		self::STATUS_AWAITING_REFUND => 'darkred',
		self::STATUS_PAID => 'darkgreen',
		self::STATUS_REFUNDED => 'darkgreen',
		self::STATUS_CANCELLED => 'black',
		self::STATUS_ACCEPTED => 'darkgreen',
	];

	public function selfCheck(): void
	{
		parent::selfCheck();
		$db = DB::getInstance();

		$this->assert(mb_strlen(trim($this->label)), 'Le libellé est vide');
		$this->assert(mb_strlen(trim($this->label)) <= 500, 'Le libellé doit faire au maximum 500 caractères');
		$this->assert(!isset($this->description) || mb_strlen(trim($this->description)) <= 10000, 'La description doit faire au maximum 10.000 caractères');

		$this->assert($this->date_expiry > $this->date_created, 'La date d\'échéance doit être après la date d\'émission');

		if ($this->number && $this->exists()) {
			$this->assert(!$db->test(self::TABLE, 'id != ? AND type = ? AND year = ? AND number = ?',
				$this->id(), $this->type, $this->year, $this->number));
		}
		elseif ($this->number) {
			$this->assert(!$db->test(self::TABLE, 'type = ? AND strftime(\'%Y\', date_created) = ? AND number = ?',
				$this->type, $this->year, $this->number));
		}

		if ($this->operation_type) {
			$this->assert(array_key_exists($this->operation_type, self::OPERATION_TYPES));
		}

		$this->assert(array_key_exists($this->type, self::TYPES));
		$this->assert(array_key_exists($this->status, self::STATUSES));

		$this->assert(!isset($this->vat_exemption_code) || array_key_exists($this->vat_exemption_code, Invoices::VAT_EXEMPTIONS));

		if ($this->type === self::TYPE_CREDIT) {
			$this->assert($this->id_invoice && $this->invoice());
			$this->assert($this->invoice()->type !== self::TYPE_SELF_BILLING, 'Impossible de créer un avoir pour une autofacturation');
			$this->assert($this->invoice()->type === self::TYPE_INVOICE);
		}
	}

	public function delete(): bool
	{
		if (!$this->isDraft()) {
			throw new UserException('Il n\'est pas possible de supprimer un document qui n\'est pas en brouillon');
		}

		return parent::delete();
	}

	public function duplicate(): Invoice
	{
		if ($this->type === self::TYPE_CREDIT) {
			throw new UserException('Impossible de dupliquer un avoir');
		}

		$invoice = clone $this;
		$invoice->set('status', $invoice::STATUS_DRAFT);
		$invoice->set('number', null);
		$invoice->set('content', null);
		$invoice->set('id_invoice', null);
		$invoice->set('id_transaction', null);
		$invoice->set('date_sent', null);
		$invoice->set('provider_id', null);
		$invoice->set('provider_name', null);

		return $invoice;
	}

	public function saveAsCopyOf(Invoice $source): void
	{
		$db = DB::getInstance();
		$db->begin();
		$this->save();

		foreach ($source->iterateLines() as $line) {
			$line = clone $line;
			$line->set('id_invoice', $this->id());
			$line->save();
		}

		$db->commit();
	}

	public function save(bool $selfcheck = true): bool
	{
		if (!$this->exists()
			&& $this->client()->self_billing
			&& $this->type === self::TYPE_INVOICE) {
			$this->set('type', self::TYPE_SELF_BILLING);
		}

		return parent::save($selfcheck);
	}

	public function getCountType(): int
	{
		// Self-billing has the same numbering prefix as regular invoices
		if ($this->isSelfBilling()) {
			return self::TYPE_INVOICE;
		}

		return $this->type;
	}

	public function isQuote(): bool
	{
		return $this->type === self::TYPE_QUOTE;
	}

	public function isSelfBilling(): bool
	{
		return $this->type === self::TYPE_SELF_BILLING;
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

	/**
	 * Return previous references invoice (eg. for a quote, or for a credit note)
	 */
	public function invoice(): ?Invoice
	{
		$this->_invoice ??= Invoices::get($this->id_invoice);
		return $this->_invoice;
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
			throw new \LogicException('Cannot validate a non-draft');
		}

		$db = DB::getInstance();

		if (!$this->isQuote()) {
			$config = Config::getInstance();
			$this->assert(!empty($config->org_address), 'L\'adresse de votre organisation n\'est pas renseignée.');
			$this->assert(!empty($config->org_post_code), 'Le code postal de votre organisation n\'est pas renseigné.');
			$this->assert(!empty($config->org_city), 'La ville de votre organisation n\'est pas renseignée.');

			if ($this->client()->requiresEInvoicing()) {
				$this->assert(!empty($config->org_business_number), 'Votre organisation n\'a indiqué aucun numéro d\'entreprise (SIREN) dans la configuration générale.');
			}

			if ($db->test(Line::TABLE, 'id_invoice = ? AND vat_code = ?', $this->id(), Line::VAT_EXEMPTION_CODE)) {
				$this->assert(!empty($this->vat_exemption_text) || !empty($this->vat_exemption_code),
					'Des lignes de la factures sont exemptées de TVA, mais aucune raison d\'exemption n\'a été indiquée.');
			}
		}

		$year = (int) $this->date_created->format('Y');
		$new_number = (int) $db->firstColumn('SELECT MAX(number) FROM ' . self::TABLE . ' WHERE type = ? AND year = ? AND status != ?;', $this->type, $year, self::STATUS_DRAFT);

		if ($new_number) {
			$new_number++;
		}
		else {
			$new_number = $number;
		}

		$this->set('number', $new_number);
		$this->set('year', $year);
		$this->set('status', self::STATUS_AWAITING_SEND);
		$this->set('content', $this->exportForInvoice());
		$this->saveOnly(['number', 'status', 'content', 'year']);
	}

	public function getReference(): ?string
	{
		if (!isset($this->number)) {
			return null;
		}

		return Invoices::getInvoiceReference($this->type, $this->year, $this->number);
	}

	public function markAsSent(): void
	{
		if ($this->status !== self::STATUS_AWAITING_SEND) {
			throw new \LogicException('Cannot mark as sent: ' . $this->status);
		}

		if ($this->isQuote()) {
			$status = self::STATUS_AWAITING_VALIDATION;
		}
		elseif ($this->type === self::TYPE_CREDIT) {
			$status = self::STATUS_AWAITING_REFUND;
		}
		else {
			$status = self::STATUS_AWAITING_PAYMENT;
		}

		$this->set('status', $status);
		$this->set('date_sent', new Date);
		$this->saveOnly(['status', 'date_sent']);
	}

	public function sendEmail(): void
	{
		if ($this->isDraft()) {
			throw new \LogicException('Cannot send draft invoice');
		}

		$subject = $this->isQuote() ? 'Votre devis %s' : 'Votre facture %s';
		$subject = sprintf($subject, $this->getReference());

		$body = 'Merci de trouver ci-joint le document.';

		$xml = $this->exportAs('cii');
		$html = $this->exportAs('html');
		$facturx = $this->createFacturX($xml, $html);

		$path = sprintf('%s/%s/%s.pdf', File::CONTEXT_ATTACHMENTS, sha1(random_bytes(10)), $this->getReference());
		$file = Files::createFromString($path, $facturx);

		unset($xml, $facturx);

		Emails::appendToQueue(Emails::CONTEXT_NOTIFICATION, $this->client()->email, $subject, $body, [
			'attachments'  => [$file],
			'content_html' => $html,
		]);

		if ($this->status === self::STATUS_AWAITING_SEND) {
			$this->markAsSent();
		}
	}

	public function markAsPaid(): void
	{
		if ($this->status !== self::STATUS_AWAITING_PAYMENT) {
			throw new \LogicException('Cannot mark as paid: ' . $this->status);
		}

		if ($this->type !== self::TYPE_INVOICE) {
			throw new \LogicException('Cannot mark this type as paid: ' . $this->type);
		}

		$this->set('status', self::STATUS_PAID);
		$this->saveOnly(['status']);
	}

	public function markAsUnpaid(): void
	{
		if ($this->status !== self::STATUS_PAID) {
			throw new \LogicException('Cannot mark as paid: ' . $this->status);
		}

		if ($this->type !== self::TYPE_INVOICE) {
			throw new \LogicException('Cannot mark this type as paid: ' . $this->type);
		}

		$this->set('status', self::STATUS_AWAITING_PAYMENT);
		$this->saveOnly(['status']);
	}

	public function markAsRefunded(): void
	{
		if ($this->status !== self::STATUS_AWAITING_REFUND) {
			throw new \LogicException('Cannot mark as paid: ' . $this->status);
		}

		if ($this->type !== self::TYPE_CREDIT) {
			throw new \LogicException('Cannot mark this type as paid: ' . $this->type);
		}

		$this->set('status', self::STATUS_REFUNDED);
		$this->saveOnly(['status']);
	}

	/**
	 * Cancel an invoice/quote, if it's an invoice create a reverse credit note
	 */
	public function cancel(): ?Invoice
	{
		if ($this->type === self::TYPE_CREDIT) {
			throw new \LogicException('Cannot cancel a credit note');
		}

		$new = null;
		$db = DB::getInstance();
		$db->begin();

		if ($this->type === self::TYPE_INVOICE
			&& in_array($this->status, [self::STATUS_AWAITING_PAYMENT, self::STATUS_PAID], true)) {
			$new = $this->duplicate();
			$new->set('type', self::TYPE_CREDIT);
			$new->set('date_created', new Date);
			$new->set('label', 'Avoir pour la facture ' . $this->getReference());
			$new->set('id_invoice', $this->id());
			$new->saveAsCopyOf($this, true);
		}

		$this->set('status', self::STATUS_CANCELLED);
		$this->saveOnly(['status']);

		$db->commit();

		return $new;
	}

	public function accept(): ?Invoice
	{
		if (!$this->isQuote()) {
			throw new \LogicException('Cannot accept an invoice');
		}

		$db = DB::getInstance();

		$db->begin();

		$new = $this->duplicate();
		$new->set('type', self::TYPE_INVOICE);
		$new->set('date_created', new Date);
		$new->set('id_invoice', $this->id());
		$new->saveAsCopyOf($this);

		$this->set('status', self::STATUS_ACCEPTED);
		$this->saveOnly(['status']);

		$db->commit();

		return $new;
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['client']) && is_array($source['client'])) {
			$source['id_client'] = (int) key($source['client']);
		}

		// Some values cannot be set by the user
		unset($source['type'], $source['status'], $source['client'], $source['id_invoice'],
			$source['number'], $source['total'], $source['content'],
			$source['provider_name'], $source['provider_id']);

		return parent::importForm($source);
	}

	public function updateTotal(): void
	{
		$content = $this->content ?? $this->exportForInvoice();
		$this->set('total', Utils::moneyToInteger($content->totals->total_with_vat));
		$this->saveOnly(['total']);
	}

	public function getExport(): stdClass
	{
		return $this->content ?? $this->exportForInvoice();
	}

	/**
	 * Return invoice line as an object ready for EN16931
	 * @see https://synapx.fr/blog/champs-en-16931-expliques/ for codes
	 */
	public function exportForInvoice(): stdClass
	{
		$config = Config::getInstance();
		$plugin = Plugins::getCurrent();

		if (strlen($config->currency) !== mb_strlen($config->currency)
			|| strlen($config->currency) !== 3) {
			throw new UserException('La devise sélectionnée est invalide, merci de la modifier dans la configuration.');
		}

		$buyer = $client = $this->client()->exportForInvoice();
		$seller = Clients::exportOrgForInvoice();

		// Invert buyer and seller for auto-facturation
		if ($this->type === self::TYPE_SELF_BILLING) {
			$buyer = $seller;
			$seller = $client;
		}

		$is_buyer_pro = !empty($buyer->legal_registration_identifier->value);

		$out = (object) [
			'buyer' => $buyer,
			'seller' => $seller,
			// BT-1
			'number' => $this->getReference() ?? 'Brouillon',
			// BT-2
			'issue_date' => $this->date_created->format('Y-m-d'),
			// BT-3
			'type_code' => $this->type,
			// BT-5
			'currency_code' => $config->currency,
			// BT-9
			'payment_due_date' => $this->date_expiry ? $this->date_expiry->format('Y-m-d') : null,
			'lines' => [],
			'process_control' => (object) [
				'specification_identifier' => 'urn:cen.eu:en16931:2017',
				'business_process_type' => $this->operation_type,
			],
			// Référence acheteur. "Service exécutant" Code service pour Chorus Pro. Obligatoire pour les entités publiques marquées « Service obligatoire » dans Chorus Pro.
			'buyer_reference' => $this->buyer_ref ?? '',
			// Numéro commande acheteur. "Numéro d'engagement juridique" Texte libre. Pour Chorus Pro, indiquer ici le numéro d'engagement. Obligatoire pour les entités publiques marquées « Engagement obligatoire » dans Chorus Pro.
			'contract_reference' => $this->contract_reference ?? '',
			'notes' => [
				(object) [
					'subject_code' => 'AAI',
					'note' => $this->label,
				],
			],
		];

		if (!$this->isSelfBilling()
			&& (!empty($plugin->config->iban) || !empty($plugin->config->payment_instructions))) {
			$out->payment_instructions = (object) [
				'payment_means_type_code' => !empty($plugin->config->iban) ? 30 : 1, // 30 = Credit transfer, 1 = other
			];

			if (!empty($plugin->config->payment_instructions)) {
				$out->payment_instructions->payment_means_text = $plugin->config->payment_instructions;
			}

			if (!empty($plugin->config->iban)) {
				$out->payment_instructions->credit_transfers = (object) [
					'payment_service_provider_identifier' => $plugin->config->bic ?? '',
					'payment_account_identifier' => (object) [
						'scheme' => 'IBAN',
						'value' => $plugin->config->iban,
					],
				];
			}
		}

		// Parent invoice ID for quotes and credits
		if ($this->id_invoice
			&& in_array($this->type, [self::TYPE_QUOTE, self::TYPE_CREDIT], true)) {
			$out->preceding_invoice_reference = (object) [
				'reference' => $this->invoice()->getReference(),
				'issue_date' => $this->invoice()->date_created->format('Y-m-d'),
			];
		}

		if ($this->notes) {
			$out->notes[] = (object) [
				'subject_code' => 'OSI',
				'note' => $this->notes,
			];
		}

		// Add operation type (mandatory in France since 2026)
		if ($this->operation_type) {
			$out->notes[] = (object) [
				'subject_code' => 'REG',
				'note' => 'Nature de la facture : ' . $this->getOperationTypeLabel(),
			];
		}

		// Add mandatory mention of recovery costs (only for enterprise invoices)
		// see https://www.economie.gouv.fr/entreprises/gerer-son-entreprise-au-quotidien/gerer-sa-comptabilite-et-ses-demarches/mentions-obligatoires-dune-facture-tout-savoir
		if ($is_buyer_pro
			&& $config->country === 'FR'
			&& !$this->isQuote()) {
			$out->notes[] = (object) [
				'subject_code' => 'PMT',
				'note' => 'En cas de retard de paiement, indemnité forfaitaire légale pour frais de recouvrement de 40 euros.',
			];

			$out->notes[] = (object) [
				'subject_code' => 'PMD',
				'note' => 'Tout retard de paiement engendre une pénalité exigible à compter de la date d\'échéance, calculée sur la base de trois fois le taux d\'intérêt légal.',
			];

			$out->notes[] = (object) [
				'subject_code' => 'AAB',
				'note' => 'Les réglements reçus avant la date d\'échéance ne donneront pas lieu à escompte.',
			];
		}
		elseif ($this->isQuote()) {
			$out->notes[] = (object) [
				'subject_code' => 'OSI',
				'note' => $plugin->getConfig('quote_info') ?? Invoices::DEFAULT_QUOTE_INFO,
			];
		}

		$vat = [];
		$vat_total = '0';
		$net_total = '0';

		foreach ($this->iterateLines() as $line) {
			$e = $line->exportForInvoice();
			$out->lines[] = $e;
			$e = (object) $e;
			$e->vat_information = (object) $e->vat_information;

			$vat_total = Money::calc($vat_total, '+', $e->line_vat_amount);
			$net_total = Money::calc($net_total, '+', $e->net_amount);

			// Add VAT breakdown information, it has to be different for each exemption reason
			$vat_code = md5($e->vat_information->invoiced_item_vat_category_code
				. ($e->vat_information->exemption_reason_code ?? '')
				. $e->vat_information->invoiced_item_vat_rate);
			$vat[$vat_code] ??= (object) [
				'vat_category_code'           => $e->vat_information->invoiced_item_vat_category_code,
				'vat_category_tax_amount'     => '0', // Will be filled below
				'vat_category_taxable_amount' => '0', // Will be filled below
				'vat_category_rate'           => $e->vat_information->invoiced_item_vat_rate,
			];

			if ($line->vat_code === $line::VAT_EXEMPTION_CODE
				&& ($this->vat_exemption_code || $this->vat_exemption_code)) {
				$vat[$vat_code]->vat_exemption_reason_code = $this->vat_exemption_code;
				$vat[$vat_code]->vat_exemption_reason = $this->vat_exemption_text ?? Invoices::VAT_EXEMPTIONS[$this->vat_exemption_code];
			}

			$vat[$vat_code]->vat_category_tax_amount = Money::calc($vat[$vat_code]->vat_category_tax_amount, '+', $e->line_vat_amount);
			$vat[$vat_code]->vat_category_taxable_amount = Money::calc($vat[$vat_code]->vat_category_taxable_amount, '+', $e->net_amount);
		}

		$paid = '0.00'; // TODO

		$out->totals = (object) [
			// BT-115
			'amount_due_for_payment'   => Money::calc(Money::calc($net_total, '+', $vat_total), '-', $paid),
			// BT-106
			'sum_invoice_lines_amount' => $net_total,
			// BT-112
			'total_with_vat'           => Money::calc($net_total, '+', $vat_total),
			// BT-109
			'total_without_vat'        => $net_total,
			// BT-113
			'paid_amount'              => $paid,
			// BT-110
			'total_vat_amount'         => $vat_total,
		];

		$out->vat_break_down = array_values($vat);

		return $out;
	}

	public function exportAs(string $format, ?string $parent_format = null): string
	{
		if ($format !== 'html') {
			if ($this->status === self::STATUS_DRAFT) {
				throw new UserException('Il n\'est pas possible d\'exporter un document en brouillon');
			}
		}

		if ($format === 'facturx') {
			$xml = $this->exportAs('cii', $format);
			$html = $this->exportAs('html', $format);
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
			$tpl->assign('is_draft', $this->isDraft());
			$tpl->assign('status', $this->status);
			$tpl->assign('is_quote', $this->isQuote());
			$tpl->assign(compact('parent_format'));

			if (isset($_GET['print'])) {
				$tpl->assign('facturx_enabled', $this->canExportAsFacturX());
			}
			else {
				$tpl->assign('css', file_get_contents(__DIR__ . '/../../admin/invoice.css'));
				$tpl->assign('export', true);
			}
		}

		$tpl->assign('invoice', $this->getExport());

		if ($format === 'cii') {
			$tpl->setEscapeType('xml');
		}

		$out = $tpl->fetch(__DIR__ . '/../../templates/invoice/' . $template);

		if ($format === 'cii') {
			// [PEPPOL-EN16931-R008]-Document MUST not contain empty elements. (still status warning)
			$out = preg_replace('!<(.*)>\s*</\\1>!', '', $out);
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

		if ($this->status === self::STATUS_DRAFT && $format !== 'html') {
			throw new \LogicException('Cannot download a draft');
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

		return ($this->getReference() ?? 'Brouillon') . '.' . $extension;
	}

	/**
	 * @see https://www.ghostscript.com/blog/zugferd.html
	 */
	protected function createFacturX(string $xml, string $html): string
	{
		$signal = Plugins::fire('facturx.create', true, ['html' => $html, 'xml' => $xml], ['pdf_string' => null]);

		if ($signal && $signal->isStopped()) {
			if ($str = $signal->getOut('pdf_string')) {
				return $str;
			}
			else {
				throw new \LogicException('Signal facturx.create did not return a string');
			}
		}

		$id = 'facturx_' . sha1(random_bytes(10));
		$tmp_xml_dir = STATIC_CACHE_ROOT . '/' . $id;

		// the file MUST be called factur-x.xml, if not Prince will use its name in the PDF
		// and the PDF won't be valid (attached XML file must be named factur-x.xml)
		$tmp_xml_file = $tmp_xml_dir . '/factur-x.xml';
		$root = realpath(__DIR__ . '/../..');
		$xmp_path = $root . '/factur-x/factur-x.xmp';

		// We can't use Static_Cache class as the file MUST be called "factur-x.xml"
		// or it won't work!
		Utils::safe_mkdir($tmp_xml_dir, null, true);

		file_put_contents($tmp_xml_file, $xml);

		$cmd = Utils::getPDFCommand();
		$exec = new Exec;
		$exec->addBind($xmp_path);

		// Prince can directly create a valid Factur-X PDF using STDIN/STDOUT,
		// without temporary files for HTML and PDF, much better
		if (strpos($cmd, 'prince') === 0) {
			$cmd = Utils::getPrinceCommand('PDF/A-3a');
			$exec->setCommand($cmd);
			$exec->addParams([
				//'--fail-pdf-profile-error',
				//'--fail-pdf-tag-error',
				'--fail-missing-resources',
				'--fail-dropped-content',
				sprintf('--pdf-xmp=%s', escapeshellarg($xmp_path)),
				sprintf('--attach-data=%s',escapeshellarg($tmp_xml_file)),
				'-o - -',
			]);
		}
		// Weasyprint can also do it: https://github.com/Kozea/WeasyPrint/pull/2658
		elseif (strpos($cmd, 'weasyprint') === 0) {
			$exec->setCommand($cmd);
			$exec->addParams([
				'- -', // read from STDIN, write to STDOUT
				sprintf('--attachment=%s', escapeshellarg($tmp_xml_file)),
				'--attachment-relationship=Data',
				sprintf('--xmp-metadata=%s', escapeshellarg($xmp_path)),
				'--pdf-variant=pdf/a-3a',
			]);
		}

		try {
			if ($exec->hasCommand()) {
				$exec->setStdin($html);
				if ($exec->run() && null === $exec->getStdout() && !empty($exec->getStderr())) {
					throw new \RuntimeException(sprintf("Error running PDF command: %s\n%s", $exec->getCommand(), $exec->getStderr()));
				}

				return $exec->getStdout();
			}

			if (!Exec::quick('which gs', 1)) {
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

			return Exec::quick($cmd, 5);
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

		$cmd = Utils::getPDFCommand();

		if (0 === strpos($cmd, 'prince')) {
			return true;
		}
		elseif (0 === strpos($cmd, 'weasyprint')) {
			return true;
		}

		return (bool) Exec::quick('which gs', 1);
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


	public function getVATExemptionOptions(): array
	{
		return Invoices::VAT_EXEMPTIONS;
	}

}

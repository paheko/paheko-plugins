<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Accounting\Years;
use Paheko\Users\Users;
use Paheko\Entities\Accounting\Transaction;
use Paheko\Entities\Accounting\Line;
use Paheko\Services\Services_User;

use Paheko\Plugin\HelloAsso\Forms;
use Paheko\Plugin\HelloAsso\HelloAsso;
use Paheko\Plugin\HelloAsso\Items;

use DateTime;
use stdClass;

use KD2\DB\EntityManager as EM;

class Order extends Entity
{
	const TABLE = 'plugin_helloasso_orders';

	protected int $id;
	protected int $id_form;
	protected ?int $id_user;
	protected ?int $id_transaction;
	protected \DateTime $date;
	protected string $person;
	protected int $amount;
	protected int $status;
	protected string $raw_data;

	const STATUS_PAID = 1;
	const STATUS_WAITING = 0;

	//const PROMO_ACCOUNT_CODE = '709';

	protected ?Form $_form = null;
	protected array $_tiers = [];

	static public function getStatus(stdClass $order)
	{
		$total = $order->amount->total ?? 0;
		$paid = 0;

		if (isset($order->payments)) {
			foreach ($order->payments as $payment) {
				if ($payment->state === Payment::STATE_OK) {
					$paid += $payment->amount;
				}
			}
		}

		return $paid >= $total ? self::STATUS_PAID : self::STATUS_WAITING;
	}

	public function form(): Form
	{
		$this->_form ??= Forms::get($this->id_form);
		return $this->_form;
	}

	public function tier(int $tier_id): ?Tier
	{
		$this->_tiers[$tier_id] ??= EM::findOne(Tier::class, 'SELECT * FROM @TABLE WHERE id = ? AND id_form = ?;', $tier_id, $this->id_form);
		return $this->_tiers[$tier_id];
	}

	public function setUserId(int $id): void
	{
		if ($this->id_user) {
			return;
		}

		$db = DB::getInstance();
		$db->begin();

		foreach ($this->listPayments() as $payment) {
			$payment->set('id_user', $id);
			$payment->save();
		}

		$this->set('id_user', $id);
		$this->save();
		$db->commit();
	}

	public function getLinkedUserName(): ?string
	{
		if (!$this->id_user) {
			return null;
		}

		return Users::getName($this->id_user);
	}

	public function getPayerInfos(): array
	{
		return Payment::getPayerInfos($this->getRawPayerData());
	}

	public function getRawPayerData(): ?stdClass
	{
		$data = json_decode($this->raw_data);
		return $data->payer ?? null;
	}

	public function listItems(): array
	{
		return EM::getInstance(Item::class)->all('SELECT * FROM @TABLE WHERE id_order = ? ORDER BY id DESC;', $this->id());
	}

	public function getItem(int $id): ?Item
	{
		return EM::findOne(Item::class, 'SELECT * FROM @TABLE WHERE id_order = ? AND id = ?;', $this->id(), $id);
	}

	public function listPayments(): array
	{
		return EM::getInstance(Payment::class)->all('SELECT * FROM @TABLE WHERE id_order = ? ORDER BY id DESC;', $this->id());
	}

	public function createTransaction(HelloAsso $ha, ?int $id_creator = null): ?Transaction
	{
		$config = $ha->getConfig();
		$form = $this->form();
		$db = DB::getInstance();

		$ref = 'HELLOASSO-C' . $this->id;

		// Stop here if transaction already exists
		$found = EM::findOne(Transaction::class, 'SELECT * FROM @TABLE WHERE reference = ? LIMIT 1;', $ref);

		if ($found) {
			return $found;
		}

		if (!$form->id_year) {
			throw new UserException('La campagne n\'est pas configurée pour synchroniser avec un exercice comptable. Il faut d\'abord la configurer.');
		}

		if (!$form->payment_account_code) {
			throw new UserException('La campagne n\'a pas de compte configuré pour les paiements.');
		}

		if (!isset($config->donation_account_code)) {
			throw new UserException('Aucun compte n\'est sélectionné pour les dons, dans la configuration de l\'extension.');
		}

		if (!isset($config->provider_account_code)) {
			throw new UserException('Aucun compte n\'est sélectionné pour le prestataire HelloAsso, dans la configuration de l\'extension.');
		}

		$year = Years::get($form->id_year);

		if ($this->id_transaction) {
			throw new UserException('Cette commande a déjà une écriture liée');
		}

		$accounts = $year->accounts();

		$get_account = function (string $code) use ($accounts, $year): int {
			static $list = [];
			$list[$code] ??= $accounts->getIdFromCode($code);

			if (!$list[$code]) {
				throw new UserException(sprintf('Le compte "%s" n\'existe pas dans le plan comptable "%s"', $code, $year->chart()->label));
			}

			return $list[$code];
		};

		$transaction = new Transaction;
		$transaction->type = Transaction::TYPE_ADVANCED;
		$transaction->id_creator = $id_creator;
		$transaction->id_year = $year->id;

		$transaction->date = $this->date;
		$transaction->label = 'Commande HelloAsso n°' . $this->id();
		$transaction->reference = $ref;

		$sum = 0;

		$options_codes = $db->getAssoc('SELECT id, account_code FROM plugin_helloasso_forms_options WHERE id_form = ?;', $form->id());

		// List all items, skip free items
		$sql = 'SELECT t.account_code, t.label AS tier_label, i.*
			FROM plugin_helloasso_items i
			LEFT JOIN plugin_helloasso_forms_tiers t ON t.id = i.id_tier
			WHERE i.amount > 0 AND i.id_order = ?;';

		foreach ($db->iterate($sql, $this->id()) as $item) {
			$type = Item::TYPES_ACCOUNTS[$item->type];

			// If the item has configured account for its tier
			if ($item->account_code) {
				$code = $item->account_code;
			}
			// If the item is some kind of donation
			elseif ($type === 'donation') {
				$code = $config->donation_account_code;
			}
			// Fallback to payment
			else {
				$code = $form->payment_account_code;
			}

			$line = new Line;
			$line->label = $item->label;
			$line->reference = 'I' . $item->id;
			$line->id_account = $get_account($code);
			$line->credit = $item->amount;
			$transaction->addLine($line);

			$sum += $item->amount;

			$data = json_decode($item->raw_data);

			if (!$data || !is_object($data)) {
				continue;
			}

			/*
			// Currently we don't count discounts, but we could, in the future
			if (!empty($data->discount)) {
				$line = new Line;
				$line->label = 'Code promo ' . ($data->discount->code ?? 'inconnu');
				$line->id_account = $get_account(self::PROMO_ACCOUNT_CODE);
				$line->debit = $data->discount->amount;
				$transaction->addLine($line);

				$sum -= $data->discount->amount;
			}
			*/

			if (!isset($data->options) || !is_array($data->options)) {
				continue;
			}

			// Process options directly from JSON, we don't store them
			foreach ($data->options as $option) {
				if (!isset($option->optionId, $option->amount) || empty($option->amount)) {
					continue;
				}

				$id = $option->optionId;
				$code = $options_codes[$id] ?? $form->payment_account_code;

				$line = new Line;
				$line->label = $option->name ?? 'Option';
				$line->reference = 'O' . $id;
				$line->id_account = $get_account($code);
				$line->credit = $option->amount;
				$transaction->addLine($line);

				$sum += $option->amount;
			}
		}

		// List all payments
		$sql = 'SELECT * FROM plugin_helloasso_payments WHERE id_order = ?;';

		foreach ($db->iterate($sql, $this->id()) as $payment) {
			$line = new Line;
			$line->label = sprintf('Paiement du %s', Utils::shortDate($payment->date));
			$line->reference = 'P' . $payment->id;
			$line->id_account = $get_account($config->provider_account_code);
			$line->debit = $payment->amount;
			$transaction->addLine($line);

			$sum -= $payment->amount;
		}

		if ($sum !== 0) {
			throw new \LogicException('Unbalanced transaction');
		}

		return $transaction;
	}

	public function importData(HelloAsso $ha, ?int $id_creator, bool $create_users = true, bool $create_subscriptions = true, bool $create_transaction = true): void
	{
		$list = Items::list($this, $ha);
		$list->setPageSize(null);

		$db = DB::getInstance();
		$db->begin();

		$users = [];

		foreach ($list->iterate() as $item) {
			// Find or create user ID
			if ($create_users
				&& !$item->id_user
				&& $item->create_user !== Tier::NO_USER_ACTION) {

				if (isset($item->matching_user)) {
					$item->id_user = $item->matching_user->id;
				}
				elseif (isset($item->new_user)
					&& $item->create_user === Tier::CREATE_UPDATE_USER) {
					$user = Users::create();
					$user->importForm($item->new_user);
					$user->save();
					$item->id_user = $user->id();
				}

				if ($item->id_user) {
					$db->preparedQuery(sprintf('UPDATE %s SET id_user = ? WHERE id = ? AND id_user IS NULL;', Item::TABLE), $item->id_user, $item->id);
				}
			}

			// Create subscription
			if ($create_subscriptions
				&& !$item->id_subscription
				&& $item->id_user
				&& $item->id_fee) {
				$id_service = $db->firstColumn('SELECT id_service FROM services_fees WHERE id = ?;', $item->id_fee);
				$sub = Services_User::create($item->id_user, $id_service, $item->id_fee);
				$sub->importForm(['id_service' => $id_service, 'date' => $this->date]);
				$sub->save();
				$item->id_subscription = $sub->id();
				$db->preparedQuery(sprintf('UPDATE %s SET id_subscription = ? WHERE id = ? AND id_subscription IS NULL;', Item::TABLE), $item->id_subscription, $item->id);
			}

			if ($item->id_user) {
				$users[] = $item->id_user;
			}
		}

		if ($this->id_user) {
			$users[] = $this->id_user;
		}

		$users = array_unique($users);

		if ($create_transaction
			&& !$this->id_transaction) {
			$transaction = $this->createTransaction($ha, $id_creator);

			$transaction->save();
			$transaction->updateLinkedUsers($users);

			$this->set('id_transaction', $transaction->id());
			$this->save();
		}

		$db->commit();
	}

	public function hasAllSubscriptions(): ?bool
	{
		if ($this->form()->type !== 'Membership') {
			return null;
		}

		$db = DB::getInstance();
		$sql = sprintf('SELECT 1 FROM %s i INNER JOIN %s t ON i.id_tier = t.id WHERE t.id_fee IS NOT NULL AND i.id_subscription IS NULL AND i.type = \'Membership\' AND i.id_order = ?;', Item::TABLE, Tier::TABLE);
		return $db->firstColumn($sql, $this->id()) ? false : true;
	}
}

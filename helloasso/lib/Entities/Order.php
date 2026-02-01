<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;
use Paheko\Users\Users;

use Paheko\Plugin\HelloAsso\Forms;

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

	protected ?Form $_form = null;
	protected array $_tiers = [];

	static public function getStatus(stdClass $order)
	{
		$total = $order->amount->total ?? 0;
		$paid = 0;

		if (isset($order->payments)) {
			foreach ($order->payments as $payment) {
				if ($payment->state == Payment::STATE_OK) {
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

	public function createTransaction(): Transaction
	{
		$form = $this->form();

		if (!$form->id_year) {
			throw new \RuntimeException('Cannot create transaction: no year has been specified');
		}

		if ($this->id_transaction) {
			throw new \RuntimeException('This order already has a transaction');
		}

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
		$transaction->id_creator = null;
		$transaction->id_year = $target->id_year;

		$transaction->date = $this->date;
		$transaction->label = 'Commande HelloAsso n°' . $this->id();
		$transaction->reference = 'HELLOASSO-' . $this->id;

		$tiers = [];

		// List all items, skip free items
		$sql = 'SELECT t.account_code, i.*
			FROM plugin_helloasso_items i
			LEFT JOIN plugin_helloasso_forms_tiers t ON t.id = i.id_tier
			WHERE i.amount > 0 AND i.id_order = ?;';

		foreach ($db->iterate($sql, $this->id()) as $item) {
			if (!$item->id_tier) {
				throw new \LogicException('Item does not have a tier ID: ' . $item->raw_data);
				//continue;
			}

			if (!$item->account_code) {
				throw new \RuntimeException('No account has been specified for this type: ' . $item->type);
			}

			$line = new Line;
			$line->label = $item->label;
			$line->reference = 'I' . $item->id;
			$line->id_account = $get_account($tier->account_code);
			$line->credit = $item->amount;
			$transaction->addLine($line);

			$sum += $item->amount;
		}

		// List all options, skip free options
		$sql = 'SELECT to.account_code, o.*
			FROM plugin_helloasso_items_options o
			LEFT JOIN plugin_helloasso_forms_tiers_options to ON to.id = o.id_option
			WHERE o.amount > 0 AND o.id_order = ?;';

		foreach ($db->iterate($sql, $this->id()) as $option) {
			$line = new Line;
			$line->label = $option->label ?? 'Option';
			$line->reference = 'O' . $item->id;
			$line->id_account = $get_account($option->account_code);
			$line->credit = $option->amount;
			$transaction->addLine($line);

			$sum += $option->amount;
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

		$transaction->save();
		$this->set('id_transaction', $transaction->id());
		$this->save();
	}
}

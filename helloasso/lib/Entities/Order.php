<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;
use Paheko\Users\Users;

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

	public function listPayments(): array
	{
		return EM::getInstance(Payment::class)->all('SELECT * FROM @TABLE WHERE id_order = ? ORDER BY id DESC;', $this->id());
	}

	public function createTransaction(Target $target): Transaction
	{
		if (!$target->id_year) {
			throw new \RuntimeException('Cannot create transaction: no year has been specified');
		}

		if ($this->id_transaction) {
			throw new \RuntimeException('This order already has a transaction');
		}

		$accounts = $target->listAccountsByType();

		if (!isset($accounts[TargetAccount::TYPE_THIRDPARTY])) {

		}

		$transaction = new Transaction;
		$transaction->type = Transaction::TYPE_ADVANCED;
		$transaction->id_creator = null;
		$transaction->id_year = $target->id_year;

		$transaction->date = $this->date;
		$transaction->label = 'Commande HelloAsso n°' . $this->id();
		$transaction->reference = $this->id;

		foreach ($this->listItems() as $item) {
			if (!isset($accounts[$item->type])) {
				throw new \RuntimeException('No account has been specified for this type: ' . $item->type);
			}

			$line = new Line;
			$line->label = $item->label;
			$line->reference = $item->id;
			$line->id_account = $accounts[$item->type];
			$transaction->addLine($line);

			$sum = $transaction->sum();

			$line = new Line;
			$line->label = '';
		}

		$transaction->save();
		$this->set('id_transaction', $transaction->id());
		$this->save();
	}
}

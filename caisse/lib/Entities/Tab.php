<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\DB;
use Paheko\UserException;

use Paheko\Plugin\Caisse\POS;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Tabs;
use Paheko\Entity;
use Paheko\Utils;
use Paheko\ValidationException;

class Tab extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'tabs';

	protected ?int $id;
	protected int $session;
	protected \DateTime $opened;
	protected ?\DateTime $closed = null;
	protected ?string $name = null;
	protected ?int $user_id = null;

	public int $total;

	const PAYMENT_STATUS_DEBT = 0;
	const PAYMENT_STATUS_PAID = 1;

	const ITEM_TYPE_PRODUCT = 0;
	const ITEM_TYPE_PAYOFF = 1;

	public function load(array $data): self
	{
		parent::load($data);
		$this->total = $this->total();
		return $this;
	}

	public function total(): int
	{
		$db = DB::getInstance();
		return $db->firstColumn(POS::sql('SELECT SUM(qty*price) FROM @PREFIX_tabs_items WHERE tab = ?;'), $this->id()) ?: 0;
	}

	public function getRemainder(): int
	{
		return (int) DB::getInstance()->firstColumn(POS::sql('SELECT
			COALESCE((SELECT SUM(price * qty) FROM @PREFIX_tabs_items WHERE tab = ?), 0)
			- COALESCE((SELECT SUM(amount) FROM @PREFIX_tabs_payments WHERE tab = ?), 0);'), $this->id, $this->id);
	}

	public function addItemByCode(string $code): void
	{
		$code = trim($code);
		$code = preg_replace('/\s+/', '', $code);

		if (!ctype_digit($code)) {
			throw new UserException('Code barre invalide : ' . $code);
		}

		$id = DB::getInstance()->firstColumn(POS::sql('SELECT id FROM @PREFIX_products WHERE code = ? ORDER BY id LIMIT 1;'), $code);

		if (!$id) {
			throw new UserException('Code barre inconnu : ' . $code);
		}

		$this->addItem($id);
	}

	public function addItem(int $id, string $user_weight = null, int $price = null, int $type = self::ITEM_TYPE_PRODUCT)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$db = DB::getInstance();
		$product = $db->first(POS::sql('SELECT p.*, c.name AS category_name, c.account AS category_account
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE p.id = ?'), $id);

		if (!$product) {
			throw new UserException('This product does not exist: ' . $id);
		}

		$weight = $product->weight;
		$price ??= (int)$product->price;

		if ($weight === Product::WEIGHT_BASED_PRICE) {
			$weight = Utils::weightToInteger($user_weight);

			// Cents * grams = Centsgrams / 1000 = cents/kg
			$price = ($price * $weight) / 1000;
		}
		elseif ($weight === Product::WEIGHT_REQUIRED) {
			$weight = Utils::weightToInteger($user_weight);
		}

		return $db->insert(POS::tbl('tabs_items'), [
			'tab'           => $this->id,
			'product'       => (int)$product->id,
			'qty'           => (int)$product->qty,
			'price'         => $price,
			'weight'        => $weight,
			'name'          => $product->name,
			'category_name' => $product->category_name,
			'description'   => $product->description,
			'account'       => $product->category_account,
			'type'          => $type,
		]);
	}

	public function removeItem(int $id)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		return DB::getInstance()->delete(POS::tbl('tabs_items'), 'id = ? AND tab = ?', $id, $this->id);
	}

	public function updateItemQty(int $id, int $qty)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs_items'),
			['qty' => $qty],
			sprintf('id = %d AND tab = %d', $id, $this->id));
	}

	public function updateItemWeight(int $id, string $weight)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$weight = Utils::weightToInteger($weight);

		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs_items'),
			['weight' => $weight],
			sprintf('id = %d AND tab = %d', $id, $this->id));
	}

	public function updateItemPrice(int $id, string $price)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$price = Utils::moneyToInteger($price);

		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs_items'),
			['price' => $price],
			sprintf('id = %d AND tab = %d', $id, $this->id));
	}

	public function listItems()
	{
		return DB::getInstance()->get(POS::sql('SELECT ti.*,
			ti.qty * ti.price AS total,
			GROUP_CONCAT(pm.method, \',\') AS methods
			FROM @PREFIX_tabs_items ti
			LEFT JOIN @PREFIX_products p ON ti.product = p.id
			LEFT JOIN @PREFIX_categories c ON c.id = p.category
			LEFT JOIN @PREFIX_products_methods pm ON pm.product = p.id
			WHERE ti.tab = ?
			GROUP BY ti.id
			ORDER BY ti.id;'), $this->id);
	}

	public function pay(int $method_id, int $amount, ?string $reference, bool $auto_close = true): void
	{
		if ($this->closed) {
			throw new UserException('Il n\'est pas possible de modifier une note clôturée.');
		}

		if ('' === trim($reference)) {
			$reference = NULL;
		}

		$remainder = $this->getRemainder();

		if ($amount > $remainder) {
			throw new UserException('Il n\'est pas possible d\'encaisser un montant supérieur au reste à payer.');
		}

		$options = $this->listPaymentOptions();
		$option = $options[$method_id] ?? null;

		if (!$option) {
			throw new UserException('Ce moyen de paiement n\'est pas disponible.');
		}

		if (empty($option->amount)) {
			throw new UserException('Ce moyen de paiement ne peut pas être utilisé pour cette note');
		}
		elseif ($amount > $option->amount) {
			$a = $option->amount;
			throw new UserException(sprintf('Ce moyen de paiement ne peut être utilisé pour un montant supérieur à %d,%02d€', (int) ($a/100), (int) ($a%100)));
		}

		if (null !== $reference && $option->type !== Method::TYPE_TRACKED) {
			throw new UserException('Référence indiquée pour un règlement en espèces : vouliez-vous enregistrer un règlement par chèque ?');
		}

		DB::getInstance()->insert(POS::tbl('tabs_payments'), [
			'tab'       => $this->id,
			'method'    => $method_id,
			'amount'    => $amount,
			'reference' => $reference,
			'account'   => $option->account,
			'status'    => $option->type === Method::TYPE_DEBT ? self::PAYMENT_STATUS_DEBT : self::PAYMENT_STATUS_PAID,
		]);

		if ($remainder - $amount === 0 && $auto_close) {
			$this->close();
		}
	}

	public function removePayment(int $id)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		return DB::getInstance()->delete(POS::tbl('tabs_payments'), 'id = ? AND tab = ?', $id, $this->id);
	}

	public function listPayments()
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*,
			m.name AS method_name
			FROM @PREFIX_tabs_payments tp
			LEFT JOIN @PREFIX_methods m ON m.id = tp.method
			WHERE tp.tab = ?;'), $this->id);
	}

	public function listPaymentOptions()
	{
		$remainder = $this->getRemainder();
		return DB::getInstance()->getGrouped(POS::sql('SELECT id, *,
			CASE
				WHEN max IS NOT NULL AND max > 0 AND paid >= max THEN 0 -- We cannot use this payment method, we paid the max allowed amount with it
				WHEN max IS NOT NULL AND max > 0 AND payable > max THEN max -- We have to pay more than max allowed, then just return max
				WHEN min IS NOT NULL AND payable < min THEN 0 -- We cannot use as the minimum required amount has not been reached
				ELSE MIN(:left, payable) END AS amount
			FROM (SELECT m.*, SUM(pt.amount) AS paid, SUM(i.qty * i.price) AS payable
				FROM @PREFIX_methods m
				INNER JOIN @PREFIX_products_methods pm ON pm.method = m.id
				INNER JOIN @PREFIX_tabs_items i ON i.product = pm.product AND i.tab = :id
				LEFT JOIN @PREFIX_tabs_payments AS pt ON pt.tab = i.tab AND m.id = pt.method
				WHERE m.enabled = 1
				GROUP BY m.id
			);'), ['id' => $this->id, 'left' => $remainder]);
	}

	public function rename(string $new_name, ?int $user_id) {
		$new_name = trim($new_name);
		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs'), ['name' => $new_name, 'user_id' => $user_id], $db->where('id', $this->id));
	}

	public function renameItem(int $id, string $name) {
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		return DB::getInstance()->update(POS::tbl('tabs_items'), ['name' => trim($name)], sprintf('id = %d AND tab = %d', $id, $this->id));
	}

	public function close() {
		$remainder = $this->getRemainder();

		if ($remainder != 0) {
			throw new UserException(sprintf("Impossible de clôturer la note: reste %s € à régler.", $remainder / 100));
		}

		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = datetime(\'now\',\'localtime\') WHERE id = ?;'), [$this->id]);
	}

	public function reopen() {
		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = NULL WHERE id = ?;'), [$this->id]);
	}

	public function delete(): bool {
		$db = DB::getInstance();
		if ($db->count(POS::tbl('tabs_items'), 'tab = ?', $this->id) && $db->test(POS::tbl('tabs'), 'closed IS NULL AND id = ?', $this->id)) {
			throw new UserException('Impossible de supprimer une note qui n\'est pas close');
		}

		return parent::delete();
	}

	public function getUserDebt(): int
	{
		if (empty($this->user_id)) {
			return 0;
		}

		return Tabs::getUnpaidDebtAmount($this->user_id);
	}

	public function addDebt(string $account, int $amount): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$db = DB::getInstance();
		$sql = POS::sql('SELECT p.id
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE c.account = ? LIMIT 1;');

		// Get first product matching debt account
		$product_id = $db->firstColumn($sql, $account);

		// Automatically create missing product
		if (!$product_id) {
			$product = Products::createAndSaveForDebtAccount($account);
			$product_id = $product->id();
		}

		$this->addItem($product_id, null, $amount, self::ITEM_TYPE_PAYOFF);
	}

	public function addUserDebt(): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$list = Tabs::listDebtsHistory($this->user_id);
		$list->setPageSize(null);
		$due = [];

		// Create list of debts, by third-party account
		foreach ($list->iterate() as $item) {
			$due[$item->account] ??= 0;

			if ($item->type === 'debt') {
				$due[$item->account] += $item->amount;
			}
			else {
				$due[$item->account] -= $item->amount;
			}
		}

		// Remove accounts where they are at zero
		$due = array_filter($due);

		foreach ($due as $account => $amount) {
			$this->addDebt($account, $amount);
		}
	}
}

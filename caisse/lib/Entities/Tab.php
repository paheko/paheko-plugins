<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\DB;
use Paheko\UserException;

use Paheko\Plugin\Caisse\Methods;
use Paheko\Plugin\Caisse\POS;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Tabs;
use Paheko\Plugin\Caisse\Sessions;
use Paheko\Entity;
use Paheko\Utils;
use Paheko\ValidationException;

use KD2\DB\EntityManager as EM;

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

	protected ?Session $_session = null;

	public function load(array $data): self
	{
		parent::load($data);
		$this->total = $this->total();
		return $this;
	}

	public function session(?Session $session = null): Session
	{
		if (null !== $session) {
			$this->_session = $session;
		}
		elseif (null === $this->_session) {
			$this->_session = Sessions::get($this->session);
		}

		return $this->_session;
	}

	public function total(): int
	{
		$db = DB::getInstance();
		return (int) $db->firstColumn(POS::sql('SELECT SUM(total) FROM @PREFIX_tabs_items WHERE tab = ?;'), $this->id()) ?: 0;
	}

	public function getRemainder(): int
	{
		return (int) DB::getInstance()->firstColumn(POS::sql('SELECT
			COALESCE((SELECT SUM(total) FROM @PREFIX_tabs_items WHERE tab = ?), 0)
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

	public function addItem(int $id, ?string $user_weight = null, ?int $price = null, int $type = TabItem::TYPE_PRODUCT)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$product = Products::get($id);

		if (!$product) {
			throw new UserException('This product does not exist: ' . $id);
		}

		$parent = $this->addProductItem($product, $user_weight, $price, $type);

		foreach ($product->listLinkedProducts() as $p) {
			$this->addProductItem($p, null, null, TabItem::TYPE_PRODUCT, $parent->id());
		}
	}

	public function addProductItem(Product $product, ?string $user_weight = null, ?int $price = null, int $type = TabItem::TYPE_PRODUCT, ?int $id_parent = null): TabItem
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$weight = $product->weight;
		$price ??= (int)$product->price;
		$pricing = TabItem::PRICING_QTY;

		if ($weight === Product::WEIGHT_BASED_PRICE) {
			$weight = Utils::weightToInteger($user_weight);
			$pricing = TabItem::PRICING_QTY_WEIGHT;
		}
		elseif ($weight === Product::WEIGHT_REQUIRED) {
			$weight = Utils::weightToInteger($user_weight);
		}

		$item = new TabItem;
		$item->importForm([
			'tab'            => $this->id,
			'product'        => (int)$product->id,
			'qty'            => (int)$product->qty,
			'price'          => $price,
			'weight'         => $weight,
			'name'           => $product->name,
			'category_name'  => $product->category()->name,
			'description'    => $product->description,
			'account'        => $product->category()->account,
			'type'           => $type,
			'pricing'        => $pricing,
			'id_fee'         => $product->id_fee,
			'id_parent_item' => $id_parent,
		]);

		$item->save();
		return $item;
	}

	public function getItem(int $id): ?TabItem
	{
		return EM::findOne(TabItem::class, 'SELECT * FROM @TABLE WHERE id = ? AND tab = ?;', $id, $this->id());
	}

	public function removeItem(int $id)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$item = $this->getItem($id);

		if (!$item) {
			return;
		}

		if ($item->id_parent_item) {
			throw new UserException('Ce produit est lié à un autre produit, il ne peut être supprimé seul, il faut supprimer le produit "parent".');
		}

		$item->delete();
	}

	public function updateItemQty(int $id, int $qty): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$item = $this->getItem($id);

		// Item has vanished
		if (!$item) {
			return;
		}

		if (!$item->canChangeQty()) {
			throw new UserException('La quantité de ce produit ne peut être modifiée.');
		}

		$item->set('qty', $qty);
		$item->save();
	}

	public function updateItemWeight(int $id, string $weight): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$weight = Utils::weightToInteger($weight);

		$item = $this->getItem($id);

		// Item has vanished
		if (!$item) {
			return;
		}

		$item->set('weight', $weight);
		$item->save();
	}

	public function updateItemPrice(int $id, string $price): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		try {
			$price = Utils::moneyToInteger($price, true);
		}
		catch (\InvalidArgumentException $e) {
			throw new UserException($e->getMessage(), 0, $e);
		}

		$item = $this->getItem($id);

		// Item has vanished
		if (!$item) {
			return;
		}

		$item->set('price', $price);
		$item->save();
	}

	public function listItems()
	{
		return EM::getInstance(TabItem::class)->all('SELECT * FROM @TABLE WHERE tab = ? ORDER BY id;', $this->id());
	}

	public function isUserIdMissing(): bool
	{
		if ($this->user_id) {
			return false;
		}

		return DB::getInstance()->test(TabItem::TABLE, 'id_fee IS NOT NULL AND tab = ?', $this->id());
	}

	public function pay(int $method_id, int $amount, ?string $reference, bool $auto_close = false, bool $force_tab_name = false): void
	{
		if ($this->closed) {
			throw new UserException('Il n\'est pas possible de modifier une note clôturée.');
		}

		if ('' === trim($reference)) {
			$reference = NULL;
		}

		$remainder = $this->getRemainder();

		if ($amount >= 0 && $amount > $remainder) {
			throw new UserException('Il n\'est pas possible d\'encaisser un montant supérieur au reste à payer.');
		}
		elseif ($amount < 0 && $amount < $remainder) {
			throw new UserException('Il n\'est pas possible de rembourser un montant supérieur au reste à rembourser.');
		}

		$options = $this->listPaymentOptions();
		$option = $options[$method_id] ?? null;

		if (!$option) {
			throw new UserException('Ce moyen de paiement n\'est pas disponible.');
		}
		elseif ($option->type === Method::TYPE_DEBT && empty($this->name)) {
			throw new UserException('Il n\'est pas possible d\'enregistrer une ardoise sans nom associé.');
		}
		elseif ($option->type === Method::TYPE_CREDIT && empty($this->user_id)) {
			throw new UserException('Il n\'est pas possible d\'enregistrer un paiement par porte-monnaie sans membre associé.');
		}
		elseif (!$option->payable) {
			throw new UserException('Ce moyen de paiement ne peut pas être utilisé: ' . $option->explain);
		}
		elseif ($option->payable >= 0 && $amount > $option->payable) {
			$a = $option->payable;
			throw new UserException(sprintf('Ce moyen de paiement ne peut être utilisé pour un montant supérieur à %s€', Utils::money_format($a)));
		}
		elseif ($option->min && $amount >= 0 && $amount < $option->min) {
			$a = $option->min;
			throw new UserException(sprintf('Ce moyen de paiement ne peut être utilisé pour un montant inférieur à %s€', Utils::money_format($a)));
		}
		elseif (null !== $reference && $option->type !== Method::TYPE_TRACKED) {
			throw new UserException('Référence indiquée pour un règlement en espèces : vouliez-vous enregistrer un règlement par chèque ?');
		}

		DB::getInstance()->insert(POS::tbl('tabs_payments'), [
			'tab'       => $this->id,
			'method'    => $method_id,
			'amount'    => $amount,
			'reference' => $reference,
			'account'   => $option->account,
			'type'      => $option->type,
		]);

		if ($remainder - $amount === 0 && $auto_close) {
			$this->close($force_tab_name);
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

	public function listPaymentOptions(): array
	{
		if ($l = $this->session()->id_location) {
			$where = ' AND id_location = ' . (int)$l;
		}
		else {
			$where = '';
		}

		$db = DB::getInstance();
		$sql = POS::sql(sprintf('SELECT id, * FROM @PREFIX_methods WHERE enabled = 1 %s ORDER BY name COLLATE U_NOCASE;', $where));
		$methods = $db->getGrouped($sql);

		// List of amounts payable by method, for products
		// Wallet credit and debt payoffs are handled separately
		$sql = 'SELECT pm.method, SUM(i.total)
			FROM @PREFIX_products_methods pm
			INNER JOIN @PREFIX_tabs_items i ON i.product = pm.product
			WHERE i.tab = ?
			GROUP BY pm.method;';
		$payable_methods = $db->getAssoc(POS::sql($sql), $this->id());

		// List of amounts paid by each method
		$sql = 'SELECT method, SUM(amount) FROM @PREFIX_tabs_payments WHERE tab = ? GROUP BY method;';
		$paid_methods = $db->getAssoc(POS::sql($sql), $this->id());

		// List of totals per type of item
		$sql = POS::sql('SELECT type, SUM(total) FROM @PREFIX_tabs_items WHERE tab = ? GROUP BY type;');
		$totals = $db->getAssoc($sql, $this->id());
		$base = [TabItem::TYPE_PRODUCT => 0, TabItem::TYPE_PAYOFF => 0, TabItem::TYPE_CREDIT => 0];

		$totals = array_replace($base, $totals);

		$total = array_sum($totals);
		$total_paid = array_sum($paid_methods);
		$remainder = $total - $total_paid;

		// Walk through methods and see if we can use them and calculate the payable amount
		foreach ($methods as $id => &$method) {
			$paid = $paid_methods[$id] ?? 0;

			// Allow to pay products with linked payment method only
			$max_payable = $payable_methods[$id] ?? 0;

			// Cannot pay wallet credit with wallet or debt
			if ($method->type !== Method::TYPE_DEBT && $method->type !== Method::TYPE_CREDIT) {
				$max_payable += $totals[TabItem::TYPE_CREDIT];
			}

			// Debts cannot be paid of with a debt, but with credit or with other methods
			if ($method->type !== Method::TYPE_DEBT) {
				$max_payable += $totals[TabItem::TYPE_PAYOFF];
			}

			// If zero, this means that paid_methods[$id] does not exist, and there is no credit or payoff to pay
			if ($max_payable === 0) {
				$method->explain = 'aucun produit associé';
				$method->payable = null;
				continue;
			}

			// Subtract what has been paid already this might get us below zero
			$payable = $max_payable - $total_paid;

			// Cannot pay more than the total remainder
			$payable = min($remainder, $payable);

			if ($payable <= 0
				&& ($method->type === Method::TYPE_DEBT || $method->type === Method::TYPE_CREDIT)) {
				$method->explain = 'indisponible';
				$method->payable = null;
				continue;
			}

			// Check for account credit
			if ($method->type === Method::TYPE_CREDIT) {
				$credit = $this->getUserCredit($method->id);
				$payable = min($payable, $credit);

				// Cannot pay more than available credit
				if ($payable <= 0) {
					$method->payable = null;
					$method->explain = 'solde épuisé';
					continue;
				}
			}

			// If amount paid with this method has been attained, we can no longer pay with it
			if ($method->max && $paid >= $method->max) {
				$method->payable = null;
				$method->explain = 'maximum atteint';
				continue;
			}
			// Remainder amount is too small, discard method
			elseif ($method->min && $payable >= 0 && $payable < $method->min) {
				$method->payable = null;
				$method->explain = 'minimum : ' . Utils::money_format($method->min);
				continue;
			}

			if ($method->max !== null) {
				$payable = min($payable, $method->max);
			}

			// Allow for negative amounts (refunds) even if min is specified
			if ($method->min && $payable >= 0) {
				$payable = max($payable, $method->min);
			}

			if (!$payable) {
				$method->payable = null;
				$method->explain = 'indisponible';
				continue;
			}

			$method->payable = $payable;
		}

		unset($method);

		return $methods;
	}

	public function rename(string $new_name, ?int $user_id) {
		if ($this->closed && ($this->user_id || $this->name)) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$new_name = trim($new_name);
		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs'), ['name' => $new_name, 'user_id' => $user_id], $db->where('id', $this->id));
	}

	public function renameItem(int $id, string $name): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$item = $this->getItem($id);

		// Item has vanished
		if (!$item) {
			return;
		}

		$item->set('name', trim($name));
		$item->save();
	}

	public function close(bool $force_tab_name = false)
	{
		$remainder = $this->getRemainder();

		if ($remainder != 0) {
			throw new UserException(sprintf("Impossible de clôturer la note: reste %s € à régler.", $remainder / 100));
		}

		if (!$force_tab_name && $this->requiresName()) {
			$force_tab_name = true;
		}

		if ($force_tab_name && empty($this->name) && empty($this->user_id)) {
			throw new UserException('Impossible de clôturer la note : aucun nom n\'a été fourni.');
		}

		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = datetime(\'now\',\'localtime\') WHERE id = ?;'), [$this->id]);
	}

	public function requiresName(): bool
	{
		$db = DB::getInstance();
		return $db->test(TabItem::TABLE, 'tab = ? AND (type = ? OR type = ?)', $this->id(), TabItem::TYPE_PAYOFF, TabItem::TYPE_CREDIT)
			|| $db->test(POS::tbl('tabs_payments'), 'tab = ? AND (type = ? OR type = ?)', $this->id(), Method::TYPE_DEBT, Method::TYPE_CREDIT);
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

	public function getUserCredit(?int $id_method = null): int
	{
		if (empty($this->user_id)) {
			return 0;
		}

		$where = $id_method ? ' AND is_settled = 1 AND id_method = ' . $id_method : '';

		return Tabs::requestBalance(Method::TYPE_CREDIT, 'user_id = ?' . $where, $this->user_id);
	}

	public function getUserDebt(): int
	{
		if (empty($this->user_id)) {
			return 0;
		}

		return Tabs::requestBalance(Method::TYPE_DEBT, 'user_id = ?', $this->user_id);
	}

	public function addUserDebtAsPayoff(): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		if (!$this->user_id) {
			return;
		}

		if ($this->getUserDebt() >= 0) {
			// Don't add debt if user has no debt
			return;
		}

		$sql = 'SELECT SUM(amount) AS amount, id_method, method, account FROM (%s)
			WHERE user_id = ? AND type IN (\'debt\', \'payoff\')
			GROUP BY id_method
			HAVING SUM(amount) < 0;';

		$sql = sprintf($sql, Tabs::getUserBalancesQuery());
		$db = DB::getInstance();

		foreach ($db->iterate($sql, $this->user_id) as $item) {
			$this->addPayoff($item->amount, $item->id_method, $item->account, $item->method);
		}
	}

	public function addPayoff(int $amount, int $id_method, ?string $method_account = null, ?string $method_name = null): TabItem
	{
		if ($this->user_id && $this->getUserDebt() >= 0) {
			throw new UserException('Ce membre n\'a pas d\'ardoise à payer');
		}

		if (!isset($method_account, $method_name)) {
			$method = Methods::get($id_method);
			$method_account = $method->account;
			$method_name = $method->name;
		}

		$item = new TabItem;
		$item->importForm([
			'tab'           => $this->id,
			'qty'           => 1,
			'price'         => abs($amount),
			'name'          => 'Règlement d\'ardoise',
			'category_name' => $method_name,
			'account'       => $method_account,
			'type'          => TabItem::TYPE_PAYOFF,
			'pricing'       => TabItem::PRICING_SINGLE,
			'id_method'     => $id_method,
		]);

		$item->save();
		return $item;
	}

	public function addUserCredit(int $id_method, int $amount): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		if ($amount <= 0) {
			throw new UserException('Le montant doit être supérieur à zéro.');
		}

		$method = Methods::get($id_method);

		if (!$method->account) {
			throw new UserException('Le moyen de paiement sélectionné n\'a pas de compte indiqué au plan comptable, merci d\'en sélectionner un.');
		}

		if ($method->type !== $method::TYPE_CREDIT) {
			throw new \LogicException('This is not a credit method');
		}

		$item = new TabItem;
		$item->importForm([
			'tab'           => $this->id,
			'qty'           => 1,
			'price'         => $amount,
			'name'          => 'Crédit du porte-monnaie',
			'category_name' => $method->name,
			'account'       => $method->account,
			'type'          => TabItem::TYPE_CREDIT,
			'pricing'       => TabItem::PRICING_SINGLE,
			'id_method'     => $method->id,
		]);

		$item->save();
	}
}

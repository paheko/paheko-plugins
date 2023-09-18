<?php

namespace Paheko\Plugin\HelloAsso;

use Paheko\Plugin\HelloAsso\Entities\Form;
use Paheko\Plugin\HelloAsso\Entities\Item;
use Paheko\Plugin\HelloAsso\Entities\Chargeable;
use Paheko\Plugin\HelloAsso\Entities\Option;
use Paheko\Plugin\HelloAsso\Entities\Order;
use Paheko\Plugin\HelloAsso\API;
use Paheko\Plugin\HelloAsso\HelloAsso as HA;

use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;
use Paheko\ValidationException;
use Paheko\Entities\Payments\Payment;
use Paheko\Entities\Accounting\Transaction;
use Paheko\Entities\Users\User;
use Paheko\Plugin\HelloAsso\Payments;
use Paheko\Entities\Services\Fee;
use Paheko\Entities\Services\Service;

use KD2\DB\EntityManager as EM;

//use Paheko\Plugin\HelloAsso\Mock\MockItems;

class Items
{
	const TRANSACTION_LABEL = 'Article %s : %s';
	const CHECKOUT_TRANSACTION_LABEL = '%s : %s';
	const DONATION_LABEL = 'Don';
	const CHECKOUT_LABEL = 'Commande #%d (%s)';

	static protected array	$_exceptions = [];

	static public function get(int $id): ?Item
	{
		return EM::findOneById(Item::class, $id);
	}

	static public function list($for): DynamicList
	{
		$columns = [
			'id' => [
				'select' => 'i.id'
			],
			'id_chargeable' => [
				'label' => 'Référence',
				'select' => 'c.id'
			],
			'transactions' => [
				'label' => 'Écritures',
				'select' => sprintf('(SELECT GROUP_CONCAT(id, \';\') FROM %s t WHERE t.reference = i.id)', Transaction::TABLE, Item::TABLE)
			],
			'amount' => [
				'label' => 'Montant',
				'select' => 'i.amount'
			],
			'type' => [
				'label' => 'Type',
				'select' => 'i.type'
			],
			'label' => [
				'label' => 'Objet',
				'select' => 'i.label'
			],
			'person' => [
				'label' => 'Personne'
			],
			'id_user' => [],
			'user_name' => [
				'label' => 'Membre correspondant*',
				'select' => 'u.nom'
			],
			'numero' => [
				'select' => 'u.numero'
			],
			'options' => [
				'label' => 'Options',
				'select' => "(CASE WHEN has_options THEN 'oui' ELSE '-' END)"
			], // sprintf("(SELECT (CASE WHEN COUNT(id) > 0 THEN 'oui' ELSE '-' END) FROM %s o WHERE o.id_item = %s.id)", Option::TABLE, Item::TABLE)
			'custom_fields' => [
				'label' => 'Champs'
			],
			'service' => [
				'label' => 'Insc. Activité',
				'select' => 's.label'
			],
			'state' => [
				'label' => 'Statut'
			],
			'id_order' => [],
		];

		$tables = Item::TABLE . ' i
			LEFT JOIN ' . Chargeable::TABLE . ' c ON (c.id = i.id_chargeable)
			LEFT JOIN ' . Fee::TABLE . ' f ON (f.id = c.id_fee)
			LEFT JOIN ' . Service::TABLE . ' s ON (s.id = f.id_service)
			LEFT JOIN ' . User::TABLE . ' u ON (u.id = i.id_user)';

		$list = new DynamicList($columns, $tables);

		if ($for instanceof Order) {
			$conditions = sprintf('id_order = %d', $for->id);
			$list->setConditions($conditions);
			$list->setTitle(sprintf('Commande - %d - Articles', $for->id));
		}

		$list->setModifier(function ($row) {
			$row->state = Item::STATES[$row->state] ?? 'Inconnu';
			$row->type = Item::TYPES[$row->type] ?? 'Inconnu';

			if (isset($row->transactions)) {
				$row->transactions = explode(';', $row->transactions);
			}

			if (isset($row->custom_fields)) {
				$row->custom_fields = json_decode($row->custom_fields, true);
			}
		});

		$list->setExportCallback(function (&$row) {
			$row->amount = $row->amount ? Utils::money_format($row->amount, '.', '', false) : null;

			// Serialize custom fields as a text field
			if (isset($row->custom_fields)) {
				$row->custom_fields = implode("\n", array_map(
					function ($v, $k) { return "$k: $v"; },
					$row->custom_fields,
					array_keys($row->custom_fields)
				));
			}
		});

		$list->orderBy('id', true);
		return $list;
	}

	static public function sync(string $org_slug, $resumingPage = 1, bool $accounting = true): int
	{
		self::initSync();
		$params = [
			'pageSize'  => (int)(HA::getPageSize() / 2), // Items processing take at least twice longer
		];

		$page_count = $resumingPage;
		$ha = HA::getInstance();

		for ($i = $resumingPage; $i <= $page_count; $i++) {
			if (!$ha->stillGotTime()) {
				$ha->saveSyncProgression($i);
				return $i;
			}
			$params['pageIndex'] = $i;
			$result = API::getInstance()->listOrganizationItems($org_slug, $params);
			$page_count = $result->pagination->totalPages;

			//$result->data = MockItems::donationAndOptions();
			//$result->data = MockItems::multipleSubscriptions();
			//$result->data = array_merge(MockItems::tif(), MockItems::tif2Third(), MockItems::tifDone());

			foreach ($result->data as $order) {
				try {
					self::syncItem($order, $accounting);
				}
				catch (SyncException $e) { self::catchSyncException($e); }
			}

			if (HA::isTrial()) {
				break;
			}
		}
		return 0;
	}

	static public function syncItem(\stdClass $data, bool $accounting, ?Payment $payment = null): ?Item
	{
		$item = self::get($data->id) ?? new Item;
		$item->set('raw_data', json_encode($data));

		$data = self::transform($data);

		self::setItem($item, $data);
		$item->save();

		// Different try/catch blocks because we want to do all steps even if an exception occured
		if (Payments::hasOneAuthorized($data)) {
			try {
				Users::syncRegistration($data, (int)$item->id_form, $item, Chargeables::getType($item, $data->order->formType), $payment);
			}
			catch (SyncException $e) { self::catchSyncException($e); }
		}

		$option_entities = self::syncOptions($data, $item, $accounting);

		try {
			self::handleAccounting($item, $data, $option_entities, $accounting);
		}
		catch (SyncException $e) { self::catchSyncException($e); }

		return $item;
	}

	static protected function setItem(Item $item, \stdClass $data): void
	{
		// ToDo: add some cache for those checks
		if (!DB::getInstance()->test(Order::TABLE, 'id = ?', $data->order_id)) {
			throw new SyncException(sprintf('Tried to synchronized the item (ID: %d) of an inexisting (never synchronized?) order #%d.', $data->id, $data->order_id));
		}
		$id_form = Forms::getId($data->org_slug, $data->form_slug);
		if (!DB::getInstance()->test(Form::TABLE, 'id = ?', $id_form)) {
			throw new SyncException(sprintf('Tried to synchronized the item (ID: %d) of an inexisting (never synchronized?) order #%d.', $data->id, $id_form));
		}

		if (!$item->exists()) {
			$item->set('id', $data->id);
			$item->set('id_order', $data->order_id);
			$item->set('id_form', $id_form);
		}

		$item->set('amount', $data->amount);
		$item->set('state', $data->state);
		$item->set('price_type', Item::API_PRICE_CATEGORIES[$data->priceCategory]);
		$item->set('type', $data->type);
		$item->set('person', $data->beneficiary_label ?? $data->payer_name);
		$item->set('label', self::generateLabel($data, (int)$item->id_form));
		$item->set('custom_fields', count($data->fields) ? (object)$data->fields : null);
		$item->set('has_options', (int)isset($data->options));

		$identifier = Users::guessUserIdentifier($data->beneficiary);
		if ($identifier && ($id_user = Users::getUserId($identifier))) {
			$item->set('id_user', $id_user);
			Payments::handleBeneficiary($id_user, $data, $item->label);
		}
	}

	static protected function syncOptions(\stdClass $data, Item $item, int $accounting): array
	{
		if (!isset($data->options)) {
			return [];
		}

		$option_entities = [];
		foreach ($data->options as $option) {
			try {
				$option_entities[] = self::syncOption($option, $data, $item->id_form, $item->id, $accounting);
			}
			catch (SyncException $e) { self::catchSyncException($e); }
		}
		return $option_entities;
	}

	static protected function syncOption(\stdClass $data, \stdClass $full_data, int $id_form, int $id_item, bool $accounting): Option
	{
		$option = EM::findOne(Option::class, 'SELECT * FROM @TABLE WHERE id_item = :id_item AND label = :name AND amount = :amount', $id_item, $data->name, $data->amount) ?? new Option;
		$option->set('raw_data', json_encode($data));
		$data = self::transformOption($data);

		if (!$option->exists()) {
			$option->set('id_item', (int)$full_data->id);
			$option->set('id_order', (int)$full_data->order->id);
		}
		$option->set('price_type', Item::API_PRICE_CATEGORIES[$data->priceCategory]);
		$option->set('amount', $data->amount);
		$option->set('label', $data->name ?? Forms::getLabel($id_form));
		$option->set('custom_fields', count($data->fields) ? (object)$data->fields : null);

		$identifier = Users::guessUserIdentifier($full_data->beneficiary);
		if ($identifier && ($id_user = Users::getUserId($identifier))) {
			$option->set('id_user', $id_user);
			Payments::handleBeneficiary($id_user, $full_data, $option->label);
		}
		$option->save();

		if (Payments::hasOneAuthorized($full_data)) {
			Users::syncRegistration($full_data, $id_form, $option, Chargeables::getType($option, $full_data->order->formType));
		}

		return $option;
	}

	static protected function handleAccounting(Item $item, \stdClass $data, array $option_entities, int $accounting): void
	{
		$multi_payments = (bool)(count($data->payments) - 1);

		// Creating a transaction only if payment is already done (not pending) and accounts sets
		if ($accounting && ($multi_payments || (!$multi_payments && !$item->id_transaction)))
		{
			foreach ($data->payments as $payment_data) {

				if ($payment_data->state === Payments::AUTHORIZED_STATUS)
				{
					if ($item->amount && $item->price_type !== Item::FREE_PRICE_TYPE) {
						if (!$payment = Payments::get((int)$payment_data->id)) {
							throw new \RuntimeException(sprintf('Payment #%d matching item #%d not found.', $payment_data->id, $item->id));
						}

						if (!$payment->hasAccounted($item->getReference())) {
							self::accountChargeable((int)$item->id_form, $item, Chargeables::getType($item, $data->order->formType), $payment, new \DateTime($payment_data->date), $multi_payments);
						}
					}
					if (isset($data->options)) {
						foreach ($option_entities as $option) {
							if ($option->amount && $option->price_type !== Item::FREE_PRICE_TYPE && !$payment->hasAccounted($option->getReference())) {
								self::accountChargeable((int)$item->id_form, $option, Chargeable::SIMPLE_TYPE, $payment, new \DateTime($payment_data->date), $multi_payments);
							}
						}
					}
				}

			}
		}
	}

	static protected function accountChargeable(int $id_form, ChargeableInterface $entity, int $type, Payment $payment, \DateTime $date, bool $multi_payments = false): bool
	{
		$chargeable = Chargeables::getFromEntity($id_form, $entity, $type);
		if ($entity->getAmount() && $chargeable->id_credit_account && $chargeable->id_debit_account) {
			// ToDo: make dynamic the hard-coded TIF ratio (0.3) in order to handle any multi_payment ratio
			$amount = $multi_payments ? ($entity->getAmount() / 3) : null;
			$chargeable->account($entity, $payment, $date, self::TRANSACTION_LABEL, $amount, $multi_payments);
			return true;
		}
		return false;
	}

	static protected function transform(\stdClass $data): \stdClass
	{
		$data->id = (int) $data->id;
		$data->order_id = (int) $data->order->id;
		$data->payer_name = isset($data->payer) ? Payers::getPersonName($data->payer) : null;
		$data->payer_infos = isset($data->payer) ? Payers::formatPersonInfos($data->payer) : null;
		$data->amount = (int) $data->amount;
		$data->form_slug = $data->order->formSlug;
		$data->org_slug = $data->order->organizationSlug;

		$data->fields = [];

		if (!empty($data->customFields)) {
			foreach ($data->customFields as $field) {
				$data->fields[$field->name] = $field->answer;
			}
		}

		$data->beneficiary = isset($data->user) ? (object)array_merge($data->fields, (array)$data->user) : $data->payer;
		if (!isset($data->beneficiary->email) && ($data->payer->firstName === $data->beneficiary->firstName) && ($data->payer->lastName === $data->beneficiary->lastName) && !empty($data->payer->email)) {
			$data->beneficiary->email = $data->payer->email;
		}
		$data->beneficiary_label = isset($data->user) ? Payers::getPersonName($data->user) : null;

		// The API does not provide item infos (such as item name) for checkout!!!
		// We need to ask details of the specific order
		if ($data->order->formType === 'Checkout') {
			$order_data = API::getInstance()->listOrderItems((int)$data->order->id);
			$data->name = $order_data->items[0]->name;
		}

		return $data;
	}

	static protected function transformOption(\stdClass $data): \stdClass
	{
		$data->fields = [];

		if (!empty($data->user)) {
			$data->fields = Payers::formatPersonInfos($data->user);
		}

		if (!empty($data->customFields)) {
			foreach ($data->customFields as $field) {
				$data->fields[$field->name] = $field->answer;
			}
		}

		return $data;
	}

	static protected function initSync(): void
	{
		Users::initSync();
	}

	static protected function generateLabel(\stdClass $data, int $id_form): string
	{
		if ($data->order->formType === 'Checkout') {
			$payment = Payments::getByOrderId((int)$data->order->id);
			if (null === $payment) {
				throw new \RuntimeException(sprintf('No payment matching retrieved checkout item #%d (order #%d).', $data->id, $data->order->id));
			}

			return $data->name;
		}
		elseif (!isset($data->name))
		{
			if ($data->type === 'Donation' && $data->order->formType !== 'Donation') {
				return self::DONATION_LABEL;
			}
			else {
				return Forms::getLabel($id_form);
			}
		}
		else {
			return $data->name;
		}
	}

	static protected function catchSyncException(SyncException $e): void
	{
		self::$_exceptions[] = $e;
	}

	static public function getExceptions(): array
	{
		return self::$_exceptions;
	}

	static public function reset(): void
	{
		$sql = sprintf('DELETE FROM %s;', Item::TABLE);
		DB::getInstance()->exec($sql);
	}

	static public function listCountOpti(Order $order): DynamicList
	{
		$list = new DynamicList([], Item::TABLE);

		$conditions = sprintf('id_order = %d', $order->id);
		$list->setConditions($conditions);

		return $list;
	}
}

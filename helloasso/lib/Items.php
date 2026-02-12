<?php

namespace Paheko\Plugin\HelloAsso;

use Paheko\Plugin\HelloAsso\Entities\Form;
use Paheko\Plugin\HelloAsso\Entities\Item;
use Paheko\Plugin\HelloAsso\Entities\Order;
use Paheko\Plugin\HelloAsso\Entities\Payment;
use Paheko\Plugin\HelloAsso\Entities\Tier;
use Paheko\Plugin\HelloAsso\API;
use Paheko\Plugin\HelloAsso\HelloAsso;

use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;

use KD2\DB\EntityManager as EM;

use stdClass;

class Items
{
	static public function get(int $id): ?Item
	{
		return EM::findOneById(Item::class, $id);
	}

	static public function list($for, ?Helloasso $ha = null): DynamicList
	{
		$columns = [
			'type' => [
				'label' => 'Type',
				'select' => 'i.type',
			],
			'id' => [
				'label' => 'Numéro',
				'select' => 'i.id',
			],
			'label' => [
				'label' => 'Objet',
				'select' => 'i.label',
			],
			'amount' => [
				'label' => 'Montant',
				'select' => 'i.amount',
			],
			'person' => [
				'label' => 'Personne',
				'select' => 'o.person',
			],
			'custom_fields' => [
				'label' => 'Champs',
				'select' => 'i.custom_fields',
			],
			'id_user' => [
				'label' => 'Membre lié',
				'select' => 'i.id_user',
			],
			'id_order' => [],
			'id_tier' => [],
		];

		$tables = sprintf('%s AS i INNER JOIN %s o ON o.id = i.id_order LEFT JOIN %s AS t ON t.id = i.id_tier', Item::TABLE, Order::TABLE, Tier::TABLE);

		if ($for instanceof Order) {
			$columns = array_merge($columns, [
				'options' => [
					'select' => 'json_extract(i.raw_data, \'$.options\')',
				],
				'card_url' => [
					'select' => 'json_extract(i.raw_data, \'$.membershipCardUrl\')',
				],
				'first_name' => [
					'select' => 'json_extract(i.raw_data, \'$.user.firstName\')',
				],
				'last_name' => [
					'select' => 'json_extract(i.raw_data, \'$.user.lastName\')',
				],
				'create_user' => [
					'select' => 't.create_user',
				],
				'extra_fields_map' => [
					'select' => 't.fields_map',
				],
				'id_fee' => [
					'select' => 't.id_fee',
				],
				'id_subscription' => [
					'label' => 'Activité',
				],
			]);
		}

		$list = new DynamicList($columns, $tables);

		if ($for instanceof Form) {
			$conditions = sprintf('i.id_form = %d', $for->id);
			$list->setConditions($conditions);
			$list->setTitle(sprintf('%s - Items', $for->name));
		}
		elseif ($for instanceof Order) {
			$conditions = sprintf('i.id_order = %d', $for->id);
			$list->setConditions($conditions);
			$list->setTitle(sprintf('Commande - %d - Items', $for->id));
		}

		$tiers = [];

		$list->setModifier(function ($row) use ($ha, $tiers) {
			$row->type_label = Item::TYPES[$row->type] ?? 'Inconnu';
			$row->type_color = Item::TYPES_COLORS[$row->type] ?? '';

			if (isset($row->custom_fields)) {
				$row->custom_fields = json_decode($row->custom_fields, true);
			}

			if (isset($row->options)) {
				$row->options = json_decode($row->options) ?? [];
				$row->options = array_map([self::class, 'normalizeOption'], $row->options);
			}

			$row->new_user_url = '';

			if ($ha
				&& $row->type === 'Membership'
				&& !isset($row->id_user)
				&& isset($row->first_name, $row->last_name)) {
				$data = ['firstName' => $row->first_name, 'lastName' => $row->last_name];
				$row->matching_user = $ha->findMatchingUser((object) $data);

				if (!$row->matching_user) {
					$tiers[$row->id_tier] ??= Forms::getTier($row->id_tier);
					$data = array_merge($data, $row->custom_fields);
					$user = $ha->getMappedUser((object)$data, $tiers[$row->id_tier]->fields_map);
					$row->new_user = $user;

					$user['redirect'] = sprintf('%s&item_id=%d&item_set_user_id=%%d', Utils::getSelfURI(), $row->id);
					$row->new_user_url = '!users/new.php?' . http_build_query($user);
				}
			}
		});

		$list->setExportCallback(function (&$row) {
			$row->amount = $row->amount ? Utils::money_format($row->amount, '.', '', false) : null;

			// Serialize custom fields as a text field
			if (isset($row->custom_fields)) {
				$row->custom_fields = implode("\n", array_map(function ($v, $k) { return "$k: $v"; },
					$row->custom_fields, array_keys($row->custom_fields)));
			}
		});

		$list->orderBy('id', true);
		return $list;
	}

	static public function normalizeOption(?stdClass $option): ?stdClass
	{
		if (null === $option) {
			return null;
		}

		return (object) [
			'id_option'     => $option->optionId,
			'amount'        => $option->amount,
			'label'         => $option->name,
			'custom_fields' => HelloAsso::normalizeCustomFields($option->customFields),
			'raw_data'      => json_encode($option),
		];
	}

/*
	static public function sync(string $org_slug): void
	{
		$params = [
			'pageSize'  => HelloAsso::getPageSize(),
		];

		$page_count = 1;

		for ($i = 1; $i <= $page_count; $i++) {
			$params['pageIndex'] = $i;
			$result = API::getInstance()->listOrganizationItems($org_slug, $params);
			$page_count = $result->pagination->totalPages;

			foreach ($result->data as $order) {
				self::syncItem($order);
			}
		}
	}
	*/

	static public function syncItem(stdClass $data, Order $order): void
	{
		$entity = self::get($data->id) ?? new Item;

		$entity->set('raw_data', json_encode($data));

		$data = self::transform($data);
		$name = $data->name ?? Forms::getName($order->id_form);
		$tier = null;

		if (!$entity->exists()) {
			$entity->set('id', $data->id);
			$entity->set('id_order', $order->id());
			$entity->set('id_form', $order->id_form);
			$entity->set('id_tier', $data->id_tier);

			// Some orders have items linked to tiers that have been deleted, so we can't find them
			// and we need to create them now
			if ($data->id_tier) {
				$tier = Forms::getOrCreateTier($data->id_tier, $order->id_form, $name, $data->amount, $data->type);
			}
		}

		$entity->set('amount', $data->amount);
		$entity->set('state', $data->state);
		$entity->set('type', $data->type);
		$entity->set('label', $name);
		$entity->set('custom_fields', count($data->fields) ? json_encode($data->fields) : null);

		$entity->save();
	}

	static protected function transform(\stdClass $data): \stdClass
	{
		$data->id = (int) $data->id;
		$data->id_tier = $data->tierId ?? null;
		$data->user_name = isset($data->user) ? Payment::getPayerName($data->user) : null;
		$data->amount = (int) $data->amount;
		$data->fields = [];

		if (!empty($data->user)) {
			$data->fields = Payment::getPayerInfos($data->user);
		}

		if (!empty($data->customFields)) {
			foreach ($data->customFields as $field) {
				$data->fields[$field->name] = $field->answer;
			}
		}

		return $data;
	}
}

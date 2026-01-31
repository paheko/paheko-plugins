<?php

namespace Paheko\Plugin\HelloAsso;

use Paheko\Plugin\HelloAsso\Entities\Form;
use Paheko\Plugin\HelloAsso\Entities\Item;
use Paheko\Plugin\HelloAsso\Entities\Order;
use Paheko\Plugin\HelloAsso\Entities\Payment;
use Paheko\Plugin\HelloAsso\API;

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

	static public function list($for): DynamicList
	{
		$columns = [
			'type' => [
				'label' => 'Type',
			],
			'id' => [
				'label' => 'Numéro',
			],
			'label' => [
				'label' => 'Objet',
			],
			'amount' => [
				'label' => 'Montant',
			],
			'person' => [
				'label' => 'Personne',
			],
			'custom_fields' => [
				'label' => 'Champs',
			],
			'id_user' => [
				'label' => 'Membre lié',
			],
			'options' => [
				'select' => 'json_extract(raw_data, \'$.options\')',
			],
			'id_order' => [],
			'card_url' => [
				'select' => 'json_extract(raw_data, \'$.membershipCardUrl\')',
			],
		];

		$tables = Item::TABLE;

		if ($for instanceof Form) {
			unset($columns['custom_fields']);
			unset($columns['options']);
			unset($columns['id_user']);
		}

		$list = new DynamicList($columns, $tables);

		if ($for instanceof Form) {
			$conditions = sprintf('id_form = %d', $for->id);
			$list->setConditions($conditions);
			$list->setTitle(sprintf('%s - Items', $for->name));
		}
		elseif ($for instanceof Order) {
			$conditions = sprintf('id_order = %d', $for->id);
			$list->setConditions($conditions);
			$list->setTitle(sprintf('Commande - %d - Items', $for->id));
		}

		$list->setModifier(function ($row) {
			$row->type_label = Item::TYPES[$row->type] ?? 'Inconnu';
			$row->type_color = Item::TYPES_COLORS[$row->type] ?? '';

			if (isset($row->custom_fields)) {
				$row->custom_fields = json_decode($row->custom_fields, true);
			}

			if (isset($row->options)) {
				$row->options = json_decode($row->options) ?? [];
				$row->options = array_map([self::class, 'normalizeOption'], $row->options);
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
			'id'             => $option->optionId,
			'amount'         => $option->amount,
			'price_category' => $option->priceCategory,
			'label'          => $option->name,
			'custom_fields'  => self::normalizeCustomFields($option->customFields),
		];
	}

	static public function normalizeCustomFields(?array $fields): array
	{
		$out = [];

		foreach ($fields as $field) {
			$out[$field->name] = $field->answer ?? null;
		}

		return $out;
	}

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

	static protected function syncItem(stdClass $data): void
	{
		$entity = self::get($data->id) ?? new Item;

		$entity->set('raw_data', json_encode($data));

		$data = self::transform($data);

		if (!$entity->exists()) {
			$entity->set('id', $data->id);
			$entity->set('id_order', $data->order_id);
			$entity->set('id_form', Forms::getId($data->org_slug, $data->form_slug));
		}

		$entity->set('amount', $data->amount);
		$entity->set('state', $data->state);
		$entity->set('type', $data->type);
		$entity->set('person', $data->user_name ?? $data->payer_name);
		$entity->set('label', $data->name ?? Forms::getName($entity->id_form));
		$entity->set('custom_fields', count($data->fields) ? json_encode($data->fields) : null);

		// FIXME: handle options

		$entity->save();
	}

	static protected function transform(\stdClass $data): \stdClass
	{
		$data->id = (int) $data->id;
		$data->order_id = (int) $data->order->id;
		$data->payer_name = isset($data->payer) ? Payment::getPayerName($data->payer) : null;
		$data->payer_infos = isset($data->payer) ? Payment::getPayerInfos($data->payer) : null;
		$data->user_name = isset($data->user) ? Payment::getPayerName($data->user) : null;
		$data->amount = (int) $data->amount;
		$data->form_slug = $data->order->formSlug;
		$data->org_slug = $data->order->organizationSlug;
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

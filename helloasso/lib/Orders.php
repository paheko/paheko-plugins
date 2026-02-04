<?php

namespace Paheko\Plugin\HelloAsso;

use Paheko\Plugin\HelloAsso\Entities\Form;
use Paheko\Plugin\HelloAsso\Entities\Order;
use Paheko\Plugin\HelloAsso\Entities\Payment;
use Paheko\Plugin\HelloAsso\API;

use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;

use KD2\DB\EntityManager as EM;

class Orders
{
	static public function get(int $id): ?Order
	{
		return EM::findOneById(Order::class, $id);
	}

	static public function list(Form $form): DynamicList
	{
		$columns = [
			'id' => [
				'label' => 'Numéro',
			],
			'date' => [
				'label' => 'Date',
			],
			'amount' => [
				'label' => 'Montant',
			],
			'person' => [
				'label' => 'Personne',
			],
			'status' => [
				'label' => 'Statut',
			],
			'id_user' => [
				'label' => 'Membre lié',
			],
		];

		$tables = Order::TABLE;
		$conditions = sprintf('id_form = %d', $form->id);

		$list = new DynamicList($columns, $tables, $conditions);
		$list->setTitle(sprintf('%s - Commandes', $form->name));

		$list->setExportCallback(function (&$row) {
			$row->amount = $row->amount ? Utils::money_format($row->amount, '.', '', false) : null;
		});

		$list->orderBy('date', true);
		return $list;
	}

	static public function sync(string $org_slug): void
	{
		$params = [
			'pageSize'    => HelloAsso::getPageSize(),
			'withDetails' => 'true',
			'sortOrder'   => 'desc',
		];

		$max = 20;
		$i = 0;

		$db = DB::getInstance();
		$db->begin();

		$last_sync = HelloAsso::getInstance()->getLastSync();

		if ($last_sync) {
			$last_sync->modify('-1 month');
			$params['from'] = $last_sync->format('Y-m-d H:i:00');
			//$params['from'] = $last_sync->format('Y-m-d H:i:00');
		}

		while ($i++ < $max) {
			$result = API::getInstance()->listOrganizationOrders($org_slug, $params);

			foreach ($result->data as $order) {
				self::syncOrder($order);
			}

			$params['continuationToken'] = $result->pagination->continuationToken ?? null;

			if (empty($params['continuationToken'])) {
				break;
			}
		}

		$db->commit();
	}

	static protected function syncOrder(\stdClass $data): void
	{
		$entity = self::get($data->id) ?? new Order;

		$entity->set('raw_data', json_encode($data));

		$data = self::transform($data);

		if (!$entity->exists()) {
			$entity->set('id', $data->id);
			$entity->set('id_form', Forms::getId($data->org_slug, $data->form_slug));
		}

		$entity->set('amount', $data->amount);
		$entity->set('status', $data->status);
		$entity->set('date', $data->date);
		$entity->set('person', $data->payer_name);
		$entity->save();

		foreach ($data->payments as $payment) {
			Payments::syncPayment($payment, $entity);
		}

		foreach ($data->items as $item) {
			Items::syncItem($item, $entity);
		}
	}

	static protected function transform(\stdClass $data): \stdClass
	{
		$data->id = (int) $data->id;
		$data->date = new \DateTime($data->date);
		$data->status = Order::getStatus($data);
		$data->payer_name = isset($data->payer) ? Payment::getPayerName($data->payer) : null;
		$data->payer_infos = isset($data->payer) ? Payment::getPayerInfos($data->payer) : null;
		$data->amount = (int) ($data->amount->total ?? 0);
		$data->form_slug = $data->formSlug;
		$data->org_slug = $data->organizationSlug;

		return $data;
	}
}

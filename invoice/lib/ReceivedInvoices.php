<?php

namespace Paheko\Plugin\Invoice;

use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Plugin\Invoice\Entities\ReceivedInvoice;

use KD2\DB\EntityManager as EM;

class ReceivedInvoices
{
	static public function get(int $id): ?Invoice
	{
		return EM::findOneById(ReceivedInvoice::class, $id);
	}

	static public function getList(?string $status = null): DynamicList
	{
		$columns = [
			'id' => [],
			'type' => [
				'label' => 'Type',
			],
			'number' => [
				'label' => 'Numéro',
			],
			'issue_date' => [
				'label' => 'Date',
				'order' => 'issue_date %s, id %1$s',
			],
			'due_date' => [
				'label' => 'Échéance',
				'order' => 'due_date %s, id %1$s',
			],
			'person_name' => [
				'label' => 'Émetteur',
			],
			'provider_name' => [
				'label' => 'Plateforme',
			],
			'total' => [
				'label' => 'Total',
				'class' => 'money',
			],
			'amount_due' => [
				'label' => 'Reste à payer',
				'class' => 'money',
			],
			'status' => [
				'label' => 'Statut',
			],
		];

		$conditions = '1';
		$params = [];

		if (null !== $status) {
			$conditions .= ' AND status = ?';
			$params[] = $status;
			unset($columns['status']);
		}

		$tables = ReceivedInvoice::TABLE;

		$list = new DynamicList($columns, $tables, $conditions);
		$list->orderBy('issue_date', true);
		$list->setParameters($params);

		$list->setModifier(function (&$row) use ($status) {
			$row->type_label = ReceivedInvoice::TYPES[$row->type ?? $type] ?? null;
			$row->status_label = ReceivedInvoice::STATUSES[$row->status ?? $status];
			$row->status_color = ReceivedInvoice::STATUSES_COLORS[$row->status ?? $status];
		});

		return $list;
	}
}

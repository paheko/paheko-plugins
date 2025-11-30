<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;
use Paheko\Users\DynamicFields;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\Method;
use Paheko\Plugin\Caisse\Entities\Session;
use Paheko\Plugin\Caisse\Entities\SessionBalance;

class Sessions
{
	static public function listYears(): array
	{
		return DB::getInstance()->getAssoc(POS::sql('SELECT strftime(\'%Y\', opened), strftime(\'%Y\', opened)
			FROM @PREFIX_sessions GROUP BY strftime(\'%Y\', opened) ORDER BY opened DESC;'));
	}

	static public function open(string $user_name, array $balances, ?int $id_location): Session
	{
		$db = DB::getInstance();
		$db->begin();
		$session = new Session;
		$session->set('open_user', $user_name);
		$session->set('opened', new \DateTime);
		$session->set('id_location', $id_location);
		$session->save();

		foreach (self::listOpeningBalances($id_location) as $balance) {
			$amount = $balances[$balance->id] ?? '';

			if (trim($amount) === '') {
				throw new UserException(sprintf('Le solde "%s" ne peut être laissé vide.', $balance->name));
			}

			$amount = Utils::moneyToInteger($amount);

			$b = $session->balance($balance->id);
			$b->set('open_amount', $amount);
			$b->save();
		}

		$db->commit();

		return $session;
	}

	static public function getCurrentId(): ?int
	{
		$db = DB::getInstance();
		return $db->firstColumn(POS::sql('SELECT id FROM @PREFIX_sessions WHERE closed IS NULL ORDER BY opened DESC LIMIT 1;'));
	}

	static public function getCurrent(): ?Session
	{
		return EM::findOne(Session::class, 'SELECT * FROM @TABLE WHERE closed IS NULL ORDER BY opened DESC LIMIT 1;');
	}

	static public function get(int $id): ?Session
	{
		return EM::findOneById(Session::class, $id);
	}

	static public function list(bool $with_location): DynamicList
	{
		$columns = [
			'location' => [
				'select' => 'CASE WHEN id_location IS NULL THEN NULL ELSE l.name END',
				'label' => 'Lieu',
			],
			'id' => [
				'select' => 's.id',
				'label' => 'Num.',
			],
			'open_user' => [
				'label' => 'Responsable',
				'select' => 's.open_user',
			],
			'close_user' => [
				'select' => 's.close_user',
			],
			'opened_day' => [
				'label' => 'Jour',
				'select' => 's.opened',
			],
			'opened' => [
				'label' => 'Ouverture',
				'select' => 's.opened',
			],
			'closed' => [
				'label' => 'Clôture',
				'select' => 's.closed',
			],
			'closed_same_day' => [
				'select' => 'date(s.closed) = date(s.opened)',
			],
			'open_amount' => [
				'label' => 'Montant ouv.',
				'select' => 'open_amount',
			],
			'close_amount' => [
				'label' => 'Montant clô.',
				'select' => 'close_amount',
			],
			'total' => [
				'label' => 'Résultat',
				'select' => 'result',
			],
			'error_amount' => [
				'label' => 'Erreur',
				'select' => 'error_amount',
			],
			'tabs_count' => [
				'order' => null,
				'label' => 'Nombre de notes',
				'select' => 'nb_tabs',
			],
		];

		if (!$with_location) {
			unset($columns['location']);
		}

		// This is overly complicated to not be too slow
		$tables = '(SELECT s.*, SUM(b.open_amount) AS open_amount, SUM(b.close_amount) AS close_amount, SUM(b.error_amount) AS error_amount FROM @PREFIX_sessions s LEFT JOIN @PREFIX_sessions_balances b ON b.id_session = s.id GROUP BY s.id) AS s
			LEFT JOIN @PREFIX_locations l ON l.id = s.id_location';

		$db = DB::getInstance();
		// We hide the amount columns if we have more than one method in the balances
		// as we cannot show open and close amounts for each method
		if ($db->firstColumn('SELECT COUNT(DISTINCT id_method) FROM ' . SessionBalance::TABLE) !== 1) {
			unset($columns['open_amount']);
			unset($columns['close_amount']);
			$columns['error_amount']['label'] = 'Erreurs';
		}


		$tables = POS::sql($tables);

		$list = new DynamicList($columns, $tables);
		$list->orderBy('opened', true);
		$list->groupBy('s.id');
		$list->setCount('COUNT(DISTINCT s.id)');
		$list->setExportCallback(function (&$row) {
			$row->total = Utils::money_format($row->total, '.', '');
			$row->close_amount = Utils::money_format($row->close_amount, '.', '');
			$row->open_amount = Utils::money_format($row->open_amount, '.', '');
			$row->error_amount = $row->error_amount ? Utils::money_format($row->error_amount, '.', '') : null;
		});
		return $list;
	}

	static public function listOpeningBalances(?int $id_location): array
	{
		$where = $id_location === null ? 'AND id_location IS NULL' : 'AND id_location = ' . $id_location;
		$sql = sprintf('SELECT id, name FROM %s WHERE type = %d AND enabled = 1 %s;', Method::TABLE, Method::TYPE_CASH, $where);
		return DB::getInstance()->get($sql);
	}}

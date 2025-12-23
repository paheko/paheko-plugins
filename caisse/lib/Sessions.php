<?php

namespace Paheko\Plugin\Caisse;

use Paheko\CSV;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;
use Paheko\UserException;
use Paheko\Users\DynamicFields;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\Method;
use Paheko\Plugin\Caisse\Entities\Session;
use Paheko\Plugin\Caisse\Entities\SessionBalance;

use DateTime;
use stdClass;

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
				'select' => 'CASE WHEN s.closed IS NOT NULL THEN result ELSE NULL END',
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
			$row->close_amount = isset($row->close_amount) ? Utils::money_format($row->close_amount, '.', '') : null;
			$row->open_amount = isset($row->open_amount) ? Utils::money_format($row->open_amount, '.', '') : null;
			$row->error_amount = $row->error_amount ? Utils::money_format($row->error_amount, '.', '') : null;
		});
		return $list;
	}

	static public function listOpeningBalances(?int $id_location): array
	{
		$where = $id_location === null ? 'AND id_location IS NULL' : 'AND id_location = ' . $id_location;
		$sql = sprintf('SELECT id, name FROM %s WHERE type = %d AND enabled = 1 %s;', Method::TABLE, Method::TYPE_CASH, $where);
		return DB::getInstance()->get($sql);
	}

	static public function iterateExportLines(DateTime $start, DateTime $end, array $errors = [])
	{
		$sql = 'SELECT * FROM @TABLE WHERE closed IS NOT NULL AND date(closed) >= date(?) AND date(closed) <= date(?);';

		$sales_sql = POS::sql('SELECT COALESCE(ti.account, c.account) AS account, SUM(ti.total) AS total, ti.category_name, c.id
			FROM @PREFIX_tabs_items ti
			INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
			LEFT JOIN @PREFIX_products p ON p.id = ti.product
			LEFT JOIN @PREFIX_categories c ON c.id = p.category
			WHERE t.session = ?
			GROUP BY COALESCE(ti.account, c.account)
			HAVING SUM(ti.total) != 0
			ORDER BY account;');

		// Get account number from either the payment or the method
		$payments_sql = POS::sql('SELECT COALESCE(tp.account, m.account) AS account, SUM(tp.amount) AS total, tp.reference, m.name, m.id
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON t.id = tp.tab
			LEFT JOIN @PREFIX_methods m ON m.id = tp.method
			WHERE t.session = ?
			GROUP BY COALESCE(tp.account, m.account), tp.reference
			HAVING SUM(tp.amount) != 0
			ORDER BY account, tp.reference;');

		$total = 0;
		$transaction = null;

		$create_line = function (array $properties) use (&$total, &$transaction): ?stdClass {
			$a = $properties['amount'];
			unset($properties['amount']);

			$line = array_merge($transaction, $properties);

			if ($a === 0) {
				return null;
			}

			$line['credit'] = $a > 0 ? $a : 0;
			$line['debit'] = $a < 0 ? abs($a) : 0;

			$total += $line['credit'];
			$total -= $line['debit'];

			return (object) $line;
		};

		$i = EM::getInstance(Session::class)->iterate($sql, $start, $end);
		$db = DB::getInstance();

		foreach ($i as $session) {
			$transaction = [
				'id'             => null,
				'type'           => 'Avancé',
				'status'         => null,
				'label'          => sprintf('Session de caisse n°%d', $session->id),
				'date'           => $session->closed->format('d/m/Y'),
				'notes'          => null,
				'reference'      => sprintf('POS-SESSION-%d', $session->id),
				'line_id'        => null,
				'account'        => null,
				'credit'         => null,
				'debit'          => null,
				'line_reference' => null,
				'line_label'     => null,
				'reconciled'     => '',
				'sid'            => $session->id,
			];

			$total = 0;

			// Payments
			foreach ($db->iterate($payments_sql, $session->id) as $payment) {
				if (!$payment->account && !$payment->id) {
					// The payment method no longer exists, and never had an account:
					// We can't change that, so just consider it as a balance error
					continue;
				}
				elseif (!$payment->account) {
					$errors[] = sprintf('Session n°%d : le moyen de paiement "%s" n\'a pas de numéro de compte', $session->id, $payment->name);
				}

				yield $create_line([
					//'line_label'     => $payment->name, // Not very useful
					'line_reference' => $payment->reference,
					'account'        => $payment->account,
					'amount'         => $payment->total * -1, // Reverse as getting money is debit
				]);
			}

			// Sales
			// TODO: allow to have non-consolidated transactions (= one line for each product sale!)
			foreach ($db->iterate($sales_sql, $session->id) as $sale) {
				$sale->label = null;

				if (!$sale->account && !$sale->id) {
					// The sale category no longer exists, and never had an account:
					// We can't change that, so just consider it as a balance error
					$sale->account = POS::ERROR_CREDIT_ACCOUNT;
					$sale->label = sprintf('Correction automatique d\'erreur : la catégorie "%s" n\'a jamais eu de numéro de compte défini', $sale->category_name);
				}
				elseif (!$sale->account) {
					$errors[] = sprintf('Session n°%d : la catégorie "%s" n\'a pas de numéro de compte', $session->id, $sale->category_name);
				}

				yield $create_line([
					'line_label' => $sale->label,
					'account'    => $sale->account,
					'amount'     => $sale->total,
				]);
			}

			// Balance errors
			foreach ($session->listBalancesWithError() as $balance) {
				$credit = $balance->error_amount < 0 ? abs($balance->error_amount) : 0;
				$debit = $balance->error_amount > 0 ? abs($balance->error_amount) : 0;

				// This is the line for the error that is related to the method account
				yield $create_line([
					'line_label' => sprintf('%s — Erreur de caisse', $balance->name),
					'account'    => $balance->account,
					// Reverse: +5€ error = debit of 5€ of cash account
					'amount'     => $balance->error_amount * -1,
				]);

				// This is the expense/revenue account (reverse of previous line)
				yield $create_line([
					'line_label' => sprintf('%s — Erreur de caisse', $balance->name),
					'account'    => $balance->error_amount < 0 ? POS::ERROR_DEBIT_ACCOUNT : POS::ERROR_CREDIT_ACCOUNT,
					'amount'     => $balance->error_amount,
				]);
			}

			// In case the totals don't match, this is unusual and should not happen.
			// But I saw one case in La rustine POS in 2021, with 1 € difference.
			if ($total !== 0) {
				// Reverse direction
				$total *= -1;
				yield $create_line([
					'line_label' => 'Erreur de total de caisse — Correction automatique',
					'account'    => $total < 0 ? POS::ERROR_DEBIT_ACCOUNT : POS::ERROR_CREDIT_ACCOUNT,
					'amount'     => $total,
				]);
			}
		}
	}

	static public function export(string $format, \DateTime $start, \DateTime $end, bool $localized_header = false)
	{
		$name = sprintf('Export caisse compta - %s à %s', $start->format('d-m-Y'), $end->format('d-m-Y'));

		if ($localized_header) {
			$header = ['Numéro d\'écriture', 'Type', 'Statut', 'Libellé', 'Date', 'Remarques', 'Numéro pièce comptable',
				'Numéro ligne', 'Compte', 'Crédit', 'Débit', 'Référence ligne', 'Libellé ligne', 'Rapprochement'];
		}
		else {
			$header = ['id', 'type', 'status', 'label', 'date', 'notes', 'reference',
				'line_id', 'account', 'credit', 'debit', 'line_reference', 'line_label', 'reconciled'];
		}

		CSV::export($format, $name, self::iterateExportLines($start, $end), $header, function (&$row) {
			static $id = null;

			if (null !== $id && $row->sid === $id) {
				$row->type = $row->status = $row->label = $row->date = $row->reference = null;
			}

			if (null === $id || $row->sid !== $id) {
				$id = $row->sid;
			}

			$row->credit = Utils::money_format($row->credit);
			$row->debit = Utils::money_format($row->debit);

			unset($row->sid);
		});
	}

}

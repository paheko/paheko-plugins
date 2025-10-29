<?php

namespace Paheko\Plugin\Caisse;

use Paheko\CSV;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Entities\Accounting\Line;
use Paheko\Entities\Accounting\Transaction;
use Paheko\Entities\Accounting\Year;
use Paheko\Files\Files;
use Paheko\Accounting\Accounts;
use Paheko\Users\Session;

use KD2\Graphics\SVG\Bar;
use KD2\Graphics\SVG\Bar_Data_Set;
use KD2\Graphics\SVG\Plot;
use KD2\Graphics\SVG\Plot_Data;

use KD2\ErrorManager;

class POS
{
	const TABLES_PREFIX = 'plugin_pos_';

	// FIXME: change for 678/778
	const ERROR_DEBIT_ACCOUNT = '658';
	const ERROR_CREDIT_ACCOUNT = '758';

	static public function sql(string $query): string
	{
		return str_replace('@PREFIX_', self::TABLES_PREFIX, $query);
	}

	static public function tbl(string $table): string
	{
		return self::TABLES_PREFIX . $table;
	}

	static public function DynamicList(array $columns, string $tables, string $conditions = '1'): DynamicList
	{
		$list = new DynamicList($columns, self::sql($tables), $conditions);
		$list->setExportCallback(function (&$row) {
			if (isset($row->sum)) {
				$row->sum = Utils::money_format($row->sum, '.', '');
			}
		});
		$list->setPagesize(null);
		return $list;
	}

	static public function applyPeriodToList(DynamicList $list, string $period, string $column_name, string $group_all): DynamicList
	{
		if ($period === 'quarter') {
			$group = '\'T\' || CAST( (strftime(\'%m\', ' . $column_name . ') + 2) / 3 AS INT)';
			$label = 'Trimestre';
		}
		elseif ($period === 'semester') {
			$group = '\'S\' || CAST( (strftime(\'%m\', ' . $column_name . ') - 1) / 6 + 1 AS INT)';
			$label = 'Semestre';
		}
		elseif ($period === 'month') {
			$group = 'strftime(\'%Y-%m-01\', ' . $column_name . ')';
			$label = 'Mois';
		}
		elseif ($period === 'day') {
			$group = 'strftime(\'%d/%m/%Y\', ' . $column_name . ')';
			$label = 'Jour';
		}
		elseif ($period === 'weekday') {
			$group = 'CASE strftime(\'%w\', ' . $column_name . ')
					WHEN \'0\' THEN \'7-dimanche\'
					WHEN \'1\' THEN \'1-lundi\'
					WHEN \'2\' THEN \'2-mardi\'
					WHEN \'3\' THEN \'3-mercredi\'
					WHEN \'4\' THEN \'4-jeudi\'
					WHEN \'5\' THEN \'5-vendredi\'
					WHEN \'6\' THEN \'6-samedi\'
					END';
			$label = 'Jour de la semaine';
		}
		elseif ($period === 'year') {
			$group = null;
			$label = 'Année';
		}
		else {
			$list->groupBy($group_all);
			$label = 'Tout';
			$group = null;
		}

		if ($group) {
			$list->addColumn('period', [
				'select' => $group,
				'label' => $label,
				//'order' => $column_name . ' %s',
			], 0);

			$old = $list->getGroupBy();

			if ($old) {
				$group .= ', ' . $old;
			}

			$list->groupBy($group);

			$list->orderBy('period', false);
		}

		$list->setTitle($list->getTitle() . sprintf(' (%s)', $label));
		return $list;
	}

	static public function barGraph(?string $title, array $data): string
	{
		$bar = new Bar(1000, 400);
		$bar->setTitle($title);
		$i = -50;

		$color = function () use (&$i) {
			$i += 50;
			return sprintf('hsl(%d, 70%%, 60%%)', $i);
		};

		foreach ($data as $group_label => $group) {
			$set = new Bar_Data_Set($group_label);

			foreach ($group as $label => $value) {
				$set->add($value, $label, $color());
			}

			$bar->add($set);
		}

		return $bar->output();
	}

	static public function plotGraph(?string $title, array $data): string
	{
		$plot = new Plot(1000, 400);
		$plot->setTitle($title);

		$i = -50;

		$color = function () use (&$i) {
			$i += 50;
			return sprintf('hsl(%d, 60%%, %d%%)', $i, $i % 100 ? 80 : 60);
		};

		foreach ($data as $label => $values) {
			$set = new Plot_Data($values, $label, $color());
			$plot->add($set);
		}

		$plot->setLabels([1 => 'jan', 'fév', 'mar', 'avr', 'mai', 'juin', 'juil', 'août', 'sep', 'oct', 'nov', 'déc']);

		return $plot->output();
	}

	static public function syncAccounting(?int $id_creator, Year $year, ?int $only_session_id = null): int
	{
		$attach = true;
		$db = DB::getInstance();
		$db->begin();

		if ($db->test(self::sql('@PREFIX_categories'), 'account IS NULL')) {
			throw new UserException('Des catégories de produits n\'ont pas de compte associé. Merci d\'associer les catégories à des comptes du plan comptable pour pouvoir procéder à la synchronisation.');
		}

		if ($db->test(self::sql('@PREFIX_methods'), 'account IS NULL')) {
			throw new UserException('Des moyens de paiement n\'ont pas de compte associé. Merci de les associer à des comptes du plan comptable pour pouvoir procéder à la synchronisation.');
		}

		$accounts = $year->accounts();
		$accounts_ids = [];
		$accounts_ids[self::ERROR_CREDIT_ACCOUNT] = $accounts->getIdFromCode(self::ERROR_CREDIT_ACCOUNT);
		$accounts_ids[self::ERROR_DEBIT_ACCOUNT] = $accounts->getIdFromCode(self::ERROR_DEBIT_ACCOUNT);

		foreach ($accounts_ids as $code => $id) {
			if (!$id) {
				throw new UserException(sprintf('Le compte "%s" n\'existe pas dans le plan comptable. Merci de le créer.', $code));
			}
		}

		$exists = $db->getAssoc('SELECT reference, id FROM acc_transactions WHERE id_year = ? AND reference LIKE \'POS-SESSION-%\';', $year->id);

		$transaction = null;
		$row = null;
		$count = 0;

		$save_transaction = function ($transaction) use ($attach, &$count, $accounts_ids) {
			// In some rare cases, the account / product may have disappeared (WTF?!), we consider it to be an error
			$error = abs($transaction->getLinesDebitSum()) - $transaction->getLinesCreditSum();
			if ($error != 0) {
				ErrorManager::reportExceptionSilent(new \LogicException(sprintf('Missing accounts for %s: debit (%d) != credit (%d)', $transaction->reference, $transaction->getLinesDebitSum(), $transaction->getLinesCreditSum())));

				if ($error > 0) {
					$line = Line::create($accounts_ids[self::ERROR_CREDIT_ACCOUNT], abs($error), 0, 'Erreur de caisse : moyen de paiement ou produit sans compte associé');
				}
				else {
					$line = Line::create($accounts_ids[self::ERROR_DEBIT_ACCOUNT], 0, abs($error), 'Erreur de caisse inconnue : moyen de paiement ou produit sans compte associé');
				}

				$transaction->addLine($line);
			}

			$transaction->save();
			$count++;

			if ($attach) {
				$sid = (int) str_replace('POS-SESSION-', '', $transaction->reference);
				$session = Sessions::get($sid);
				$path = $transaction->getAttachementsDirectory();
				$file = Files::createFromString(sprintf('%s/session-%d.html', $path, $sid), $session->export(true, 1));
			}
		};

		foreach (self::iterateSessions($year->start_date, $year->end_date) as $row) {
			// Skip POS sessions already added as transactions
			if (array_key_exists($row->reference, $exists)) {
				continue;
			}

			// Skip if POS session ID differs
			if ($only_session_id && $row->sid !== $only_session_id) {
				continue;
			}

			// No account? This means there was no account set for the payment method / product
			// when the session was done
			// We still allow the transaction to be created, but it will be accounted as an error.
			if (empty($row->account)) {
				continue;
			}

			$accounts_ids[$row->account] ??= $accounts->getIdFromCode($row->account);

			if (!$accounts_ids[$row->account]) {
				throw new UserException(sprintf('Le compte "%s" n\'existe pas dans le plan comptable de cet exercice. Merci de le créer.', $row->account));
			}

			$label = $row->line_label;
			$account_id = $accounts_ids[$row->account];

			unset($row->id);
			unset($row->status);

			if ($transaction && $transaction->reference != $row->reference) {
				$save_transaction($transaction, $row);
				$transaction = null;

				// Make sure we create only one transaction for this session (safeguard)
				if ($only_session_id) {
					break;
				}
			}

			if (!$transaction) {
				$transaction = new Transaction;
				$transaction->id_creator = $id_creator;
				$transaction->id_year = $year->id;
				$transaction->import((array) $row);
			}

			// In case there are debit and credit on the same account, create two lines
			if ($row->debit && $row->credit) {
				$transaction->addLine(Line::create($account_id, $row->credit, 0, $label, $row->line_reference));
				$transaction->addLine(Line::create($account_id, 0, $row->debit, $label, $row->line_reference));
			}
			else {
				$transaction->addLine(Line::create($account_id, $row->credit, $row->debit, $label, $row->line_reference));
			}
		}

		if ($transaction && $row) {
			$save_transaction($transaction, $row);
		}

		$db->commit();

		return $count;
	}

	static public function iterateSessions(\DateTime $start, \DateTime $end, bool $errors_only = false)
	{
		$db = DB::getInstance();

		$errors_only = $errors_only ? 'AND account IS NULL' : '';

		// This is a complex query, beware!
		// First we aggregate all sold tab items, and payments
		// then we add (UNION ALL) all error amounts
		// we default to using the first cash account for the session location
		$sql = 'SELECT
			NULL AS id,
			\'Avancé\' AS type,
			NULL AS status,
			\'Session de caisse n°\' || s.id AS label,
			strftime(\'%%d/%%m/%%Y\', s.closed) AS date,
			NULL AS notes,
			\'POS-SESSION-\' || s.id AS reference,
			NULL AS line_id,
			lines.account AS account,
			-- Flip debit/credit if negative
			CASE
				WHEN SUM(lines.debit) < 0 THEN ABS(SUM(lines.debit))
				WHEN SUM(lines.credit) < 0 THEN 0
				ELSE SUM(lines.credit)
			END AS credit,
			CASE
				WHEN SUM(lines.credit) < 0 THEN ABS(SUM(lines.credit))
				WHEN SUM(lines.debit) < 0 THEN 0
				ELSE SUM(lines.debit)
			END AS debit,
			CASE WHEN lines.reference IS NULL THEN NULL ELSE SUBSTR(lines.reference, 1, 199) END AS line_reference,
			NULL AS line_label,
			0 AS reconciled,
			s.id AS sid
			FROM @PREFIX_sessions s
			INNER JOIN (
				SELECT session, account, SUM(total) AS credit, 0 AS debit, NULL AS reference
				FROM @PREFIX_tabs_items ti
				INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
				GROUP BY t.session, account
				UNION ALL
				SELECT session, account, 0 AS credit, SUM(amount) AS debit, reference
				FROM @PREFIX_tabs_payments tp
				INNER JOIN @PREFIX_tabs t ON t.id = tp.tab
				GROUP BY t.session, account, reference
				) AS lines
				ON lines.session = s.id
			WHERE s.closed IS NOT NULL
				AND date(s.closed) >= date(:start) AND date(s.closed) <= date(:end)
				%s
			GROUP BY s.id, lines.account, lines.reference
			HAVING (SUM(lines.debit) != 0 OR SUM(lines.credit) != 0)
			UNION ALL
			-- Add error amounts to product/charge account
			SELECT NULL AS id,
			\'Avancé\' AS type,
			NULL AS status,
			\'Session de caisse n°\' || s.id AS label,
			strftime(\'%%d/%%m/%%Y\', s.closed) AS date,
			NULL AS notes,
			\'POS-SESSION-\' || s.id AS reference,
			NULL AS line_id,
			CASE WHEN error_amount < 0 THEN :error_debit_account ELSE :error_credit_account END AS account,
			CASE WHEN error_amount > 0 THEN ABS(error_amount) ELSE 0 END AS credit,
			CASE WHEN error_amount < 0 THEN ABS(error_amount) ELSE 0 END AS debit,
			NULL AS line_reference,
			\'Erreur de caisse\' AS line_label,
			0 AS reconciled,
			s.id AS sid
			FROM @PREFIX_sessions AS s
			WHERE s.closed IS NOT NULL
				AND s.error_amount != 0
				AND date(s.closed) >= date(:start) AND date(s.closed) <= date(:end)
				%1$s
			UNION ALL
			-- Add error amounts to cash account
			SELECT NULL AS id,
			\'Avancé\' AS type,
			NULL AS status,
			\'Session de caisse n°\' || s.id AS label,
			strftime(\'%%d/%%m/%%Y\', s.closed) AS date,
			NULL AS notes,
			\'POS-SESSION-\' || s.id AS reference,
			NULL AS line_id,
			(SELECT m.account FROM @PREFIX_methods AS m WHERE m.type = 1 AND m.enabled = 1
				AND (CASE WHEN s.id_location IS NULL THEN m.id_location IS NULL ELSE m.id_location = s.id_location END) LIMIT 1) AS account,
			CASE WHEN error_amount < 0 THEN ABS(error_amount) ELSE 0 END AS credit,
			CASE WHEN error_amount > 0 THEN ABS(error_amount) ELSE 0 END AS debit,
			NULL AS line_reference,
			\'Erreur de caisse\' AS line_label,
			0 AS reconciled,
			s.id AS sid
			FROM @PREFIX_sessions AS s
			WHERE s.closed IS NOT NULL
				AND s.error_amount != 0
				AND date(s.closed) >= date(:start) AND date(s.closed) <= date(:end)
				%1$s
			ORDER BY sid, account, line_reference;';

		$sql = sprintf($sql, $errors_only);
		$sql = POS::sql($sql);

		$error_debit_account = self::ERROR_DEBIT_ACCOUNT;
		$error_credit_account = self::ERROR_CREDIT_ACCOUNT;

		return $db->iterate($sql, compact('start', 'end', 'error_debit_account', 'error_credit_account'));
	}

	static public function exportSessionsCSV(string $format, \DateTime $start, \DateTime $end, bool $localized_header = false)
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

		CSV::export($format, $name, self::iterateSessions($start, $end), $header, function (&$row) {
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

<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Config;
use Paheko\DynamicList;
use Paheko\Plugin\Invoice\Entities\Client;
use Paheko\Plugin\Invoice\Entities\Invoice;
use Paheko\Plugin\Invoice\Entities\Line;

use KD2\DB\EntityManager as EM;

class Invoices
{
	const DEFAULT_VAT_EXEMPTION = 'VATEX-FR-CGI261-5';
	const ZERO_RATE_VAT_EXEMPTION_CODE = 'VATEX-FR-FRANCHISE';

	const VAT_EXEMPTIONS = [
		// Special case, must have VAT code == Z instead of E
		'VATEX-FR-FRANCHISE'     => 'Franchise en base de TVA (auto-entrepreneur, micro-entreprise, etc.) — Art. 293 B CGI',
		'VATEX-FR-CNWVAT'        => 'Non-assujetti établi hors de France',
		'VATEX-FR-AE'            => 'Autoliquidation',
		'VATEX-FR-CGI261-1'      => 'Soins et services médicaux (médecins, chirurgiens, sages-femmes) — Art. 261-1° CGI',
		'VATEX-FR-CGI261-2'      => 'Services paramédicaux (infirmiers, kinésithérapeutes, etc.) — Art. 261-2° CGI',
		'VATEX-FR-CGI261-3'      => 'Enseignement scolaire, universitaire et formation professionnelle — Art. 261-3° CGI',
		'VATEX-FR-CGI261-4'      => 'Services à caractère sportif et éducatif — Art. 261-4° CGI',
		'VATEX-FR-CGI261-5'      => 'Organismes sans but lucratif — Art. 261-5° CGI',
		'VATEX-FR-CGI261-7'      => 'Services rendus à leurs membres par certains groupements — Art. 261-7° CGI',
		'VATEX-FR-CGI261-8'      => 'Opérations immobilières exonérées — Art. 261-8° CGI',
		'VATEX-FR-CGI261A'       => 'Activités des établissements financiers et d\'assurance — Art. 261 A CGI',
		'VATEX-FR-CGI261B'       => 'Services rendus entre membres d\'un groupement (GIE) — Art. 261 B CGI',
		'VATEX-FR-CGI261C-1'     => 'Opérations bancaires et financières — Art. 261 C-1° CGI',
		'VATEX-FR-CGI261C-2'     => 'Opérations d\'assurance et de réassurance — Art. 261 C-2° CGI',
		'VATEX-FR-CGI261C-3'     => 'Opérations sur valeurs mobilières — Art. 261 C-3° CGI',
		'VATEX-FR-CGI261D-1'     => 'Locations de terrains non aménagés et de locaux nus à usage non professionnel — Art. 261 D-1° CGI',
		'VATEX-FR-CGI261D-1BIS'  => 'Locations de logements meublés à titre de résidence principale — Art. 261 D-1° bis CGI',
		'VATEX-FR-CGI261D-2'     => 'Locations d\'immeubles à usage d\'habitation — Art. 261 D-2° CGI',
		'VATEX-FR-CGI261D-3'     => 'Locations de terres et bâtiments à usage agricole — Art. 261 D-3° CGI',
		'VATEX-FR-CGI261D-4'     => 'Locations de locaux nus à usage professionnel (sans option TVA) — Art. 261 D-4° CGI',
		'VATEX-FR-CGI261E-1'     => 'Opérations portant sur l\'or d\'investissement — Art. 261 E-1° CGI',
		'VATEX-FR-CGI261E-2'     => 'Négociation sur l\'or d\'investissement — Art. 261 E-2° CGI',
		'VATEX-FR-CGI277A'       => 'Régime suspensif de TVA (entrepôts fiscaux) — Art. 277 A CGI',
		'VATEX-FR-CGI275'        => 'Achats en franchise de TVA (exportateurs) — Art. 275 CGI',
		'VATEX-FR-298SEXDECIESA' => 'Régime particulier des agences de voyages — Art. 298 sexdecies A CGI',
		'VATEX-FR-CGI295'        => 'Exonérations dans les DOM — Art. 295 CGI',
	];

	static public function get(int $id): ?Invoice
	{
		return EM::findOneById(Invoice::class, $id);
	}

	static public function getLine(int $id): ?Line
	{
		return EM::findOneById(Line::class, $id);
	}

	static public function getList(?int $type = null, ?string $status = null): DynamicList
	{
		$columns = [
			'id' => ['select' => 'i.id'],
			'number' => [
				'label' => 'Numéro',
			],
			'date_created' => [
				'label' => 'Date',
			],
			'label' => [
				'label' => 'Objet',
			],
			'client_name' => [
				'label' => 'Client',
				'select' => 'c.name',
			],
			'status' => [
				'label' => 'Statut',
			],
			'total' => [
				'label' => 'Total',
				'class' => 'money',
			],
		];

		if ($type === Invoice::TYPE_QUOTE) {
			$conditions = 'type = ?';
			$params = [$type];
		}
		elseif ($type !== null) {
			$conditions = 'type != ?';
			$params = [Invoice::TYPE_QUOTE];
		}
		else {
			$conditions = '1';
			$params = [];
		}

		if (null !== $status) {
			$conditions .= ' AND status = ?';
			$params[] = $status;
			unset($columns['status']);
		}

		$tables = sprintf('%s AS i INNER JOIN %s AS c ON c.id = i.id_client', Invoice::TABLE, Client::TABLE);

		$list = new DynamicList($columns, $tables, $conditions);
		$list->orderBy('date_created', true);
		$list->setParameters($params);

		$list->setModifier(function (&$row) {
			$row->status_label = Invoice::STATUSES[$row->status];
			$row->status_color = Invoice::STATUSES_COLORS[$row->status];
		});

		return $list;
	}
}

<?php

namespace Paheko\Plugin\Stock_Velos;

use Paheko\DB;
use Paheko\Entity;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\DynamicList;
use Paheko\Users\Session;
use Paheko\Entities\Plugin;

use KD2\DB\Date;
use KD2\DB\EntityManager;
use KD2\Graphics\SVG\Bar;
use KD2\Graphics\SVG\Bar_Data_Set;

class Velos
{
	const A_DEMONTER = -1;

	protected int $max_etiquettes = 1000;

	protected Plugin $plugin;
	protected ?array $fields;

	const ABAQUES = [
		'Enfant' => '10',
		'Adulte' => '12.5',
	];

	protected $columns_order = array(
		'id',
		'etiquette',
		'date_entree',
		'date_sortie',
		'raison_sortie',
		'couleur',
		'modele',
		'type',
		'roues',
		'genre',
		'prix',
		'statut',
	);

	const FIELDS = [
		'etiquette'      => 'Numéro étiquette',
		'bicycode'       => 'Bicycode',
		'prix'           => 'Prix du vélo',
		'notes'          => 'Notes',
		'source'         => 'Provenance du vélo',
		'source_details' => 'Détails sur la provenance',
		'type'           => 'Type du vélo (VTT, ville, etc.)',
		'roues'          => 'Taille (26", 700, etc.)',
		'genre'          => 'Genre (Homme, Mixte, etc.)',
		'couleur'        => 'Couleur',
		'modele'         => 'Marque et modèle',
		'poids'          => 'Poids',
		'date_entree'    => 'Date d\'entrée dans le stock',
		'etat_entree'    => 'État à l\'entrée dans le stock',
		'date_sortie'    => 'Date de sortie du stock',
		'raison_sortie'  => 'Motif de sortie',
		'details_sortie' => 'Détails de sortie',
	];

	const DEFAULTS = [
		'type' => [
			'Course',
			'Mi-course',
			'Randonneuse',
			'Mini',
			'Pliant',
			'Ville',
			'VTC',
			'VTT',
			'BMX',
			'Autre',
		],
		'genre' => [
			'Diamant',
			'Mixte',
			'Enfant/Diamant',
			'Enfant/Mixte',
		],
		'roues' => [
			'700C',
			'650B',
			'26"',
			'24"',
			'20"',
			'16"',
			'Autre',
		],
		'source' => [
			'Achat',
			'Don',
			'Rachat',
			'Récupération',
			'Partenariat',
		],
		'raison_sortie' => [
			'Démonté',
			'Vendu',
			'Vendu en bourse',
			'Jeté',
		],
		'source_details' => [
			'Déchetterie',
			'Copropriété',
		],
	];

	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
	}

	public function getFields(): array
	{
		if (isset($this->fields)) {
			return $this->fields;
		}

		static $exclude_require = ['raison_sortie', 'date_sortie'];
		$statuses = (array)($this->plugin->getConfig('fields') ?? []);
		$defaults = (array)($this->plugin->getConfig('defaults') ?? []);
		$out = [];

		foreach (self::FIELDS as $name => $label) {
			$status = $statuses[$name] ?? 1;

			$field = (object) [
				'name'        => $name,
				'label'       => $label,
				'required'    => $status === 2,
				'enabled'     => $status > 0,
				'status'      => $status,
				'can_require' => !in_array($name, $exclude_require),
				'has_options' => false,
				'options'     => null,
			];

			if (isset(self::DEFAULTS[$name])) {
				$o = $defaults[$name] ?? self::DEFAULTS[$name];
				$field->has_options = true;
				$field->options = array_combine($o, $o);
			}

			$out[$name] = $field;
		}

		$this->fields = $out;

		return $this->fields;
	}

	public function addVelosDemontes(int $nb, string $source, string $source_details)
	{
		$db = DB::getInstance();

		$data = [
			'source' => trim($source),
			'source_details' => trim($source_details) ?: null,
			'date_entree' => gmdate('Y-m-d'),
			'etat_entree' => 'À démonter',
			'date_sortie' => gmdate('Y-m-d'),
			'raison_sortie' => 'Démonté',
			'details_sortie' => 'Saisie rapide de plusieurs vélos démontés',
		];

		for ($i = 0; $i < $nb; $i++) {
			$db->insert('plugin_stock_velos', $data);
		}

		return true;
	}

	static public function get(int $id): ?Velo
	{
		return EntityManager::findOneById(Velo::class, $id);
	}

	public function listVelosStock()
	{
		$columns = [
			'id' => [
				'label' => 'Num.',
			],
			'etiquette' => [
				'label' => 'Étiq.',
			],
			'type' => [
				'label' => 'Type',
			],
			'roues' => [
				'label' => 'Taille'
			],
			'genre' => [
				'label' => 'Genre'
			],
			'modele' => [
				'label' => 'Modèle'
			],
			'couleur' => [
				'label' => 'Couleur'
			],
			'prix' => [
				'label' => 'Prix'
			],
			'date_entree' => [
				'label' => 'Entrée'
			],
			'source' => [
				'label' => 'Source',
				'export' => true,
			],
			'source_details' => [
				'label' => 'Détails sur la source',
				'export' => true,
			],
			'etat_entree' => [
				'label' => 'État à l\'entrée',
				'export' => true,
			],
			'bicycode' => [
				'label' => 'Bicycode',
				'export' => true,
			],
			'notes' => [
				'label' => 'Notes',
				'export' => true,
			],
			'poids' => [
				'label' => 'Poids',
				'export' => true,
			],
		];

		// Remove disabled fields from list
		foreach ($this->getFields() as $field) {
			if (!isset($columns[$field->name])) {
				continue;
			}

			if (!$field->enabled) {
				unset($columns[$field->name]);
			}
		}

		$tables = 'plugin_stock_velos';
		$conditions = 'date_sortie IS NULL';

		$list = new DynamicList($columns, $tables, $conditions);

		if (array_key_exists('etiquette', $columns)) {
			$list->orderBy('etiquette', false);
		}
		else {
			$list->orderBy('id', true);
		}

		$list->setModifier(function (&$row) {
			$row->date_entree = Entity::filterUserDateValue($row->date_entree, Date::class);
		});
		return $list;
	}

	public function listVelosHistorique()
	{
		$columns = [
			'id' => [
				'label' => 'Num.',
			],
			'etiquette' => [
				'label' => 'Etiq.',
			],
			'type' => [
				'label' => 'Type',
			],
			'roues' => [
				'label' => 'Taille'
			],
			'genre' => [
				'label' => 'Genre'
			],
			'modele' => [
				'label' => 'Modèle'
			],
			'couleur' => [
				'label' => 'Couleur'
			],
			'prix' => [
				'label' => 'Prix'
			],
			'date_sortie' => [
				'label' => 'Sortie',
			],
			'raison_sortie' => [
				'label' => 'Motif',
			],
			'details_sortie' => [
				'label' => 'Détails sortie',
				'export' => true,
			],
			'source' => [
				'label' => 'Source',
				'export' => true,
			],
			'source_details' => [
				'label' => 'Détails sur la source',
				'export' => true,
			],
			'date_entree' => [
				'label' => 'Date entrée',
				'export' => true,
			],
			'etat_entree' => [
				'label' => 'État à l\'entrée',
				'export' => true,
			],
			'bicycode' => [
				'label' => 'Bicycode',
				'export' => true,
			],
			'notes' => [
				'label' => 'Notes',
				'export' => true,
			],
			'poids' => [
				'label' => 'Poids',
				'export' => true,
			],
		];

		// Remove disabled fields from list
		foreach ($this->getFields() as $field) {
			if (!isset($columns[$field->name]) || $field->name === 'date_sortie') {
				continue;
			}

			if (!$field->enabled) {
				unset($columns[$field->name]);
			}
		}

		$tables = 'plugin_stock_velos';
		$conditions = 'date_sortie IS NOT NULL';

		$list = new DynamicList($columns, $tables, $conditions);
		$list->orderBy('date_sortie', true);
		$list->setModifier(function (&$row) {
			$row->date_sortie = Entity::filterUserDateValue($row->date_sortie, Date::class);
			$row->date_entree = Entity::filterUserDateValue($row->date_entree, Date::class);
		});
		return $list;
	}

	public function countVelosStock()
	{
		return DB::getInstance()->firstColumn('SELECT COUNT(*) FROM plugin_stock_velos WHERE date_sortie IS NULL;');
	}

	public function countVelosHistorique()
	{
		return DB::getInstance()->firstColumn('SELECT COUNT(*) FROM plugin_stock_velos WHERE date_sortie IS NOT NULL;');
	}

	public function listStock()
	{
		$db = DB::getInstance();
		return $db->get('SELECT id, etiquette, prix FROM plugin_stock_velos WHERE date_sortie IS NULL;');
	}

	public function getValeurStock()
	{
		return DB::getInstance()->firstColumn('SELECT SUM(prix) FROM plugin_stock_velos WHERE date_sortie IS NULL AND prix > 0;');
	}

	public function getEtiquetteLibre()
	{
		return DB::getInstance()->firstColumn('SELECT COALESCE(MIN(a.etiquette) + 1, 1)
			FROM plugin_stock_velos a
			LEFT JOIN plugin_stock_velos b ON b.etiquette = a.etiquette + 1 AND b.date_sortie IS NULL
			WHERE b.etiquette IS NULL
			AND a.date_sortie IS NULL;');
	}

	public function getIdFromEtiquette($etiquette)
	{
		return DB::getInstance()->firstColumn('SELECT id FROM plugin_stock_velos WHERE date_sortie IS NULL AND etiquette = ?;', (int)$etiquette);
	}

	public function search($field, $search)
	{
		$query = 'SELECT * FROM plugin_stock_velos WHERE ';
		$db = DB::getInstance();

		if ($field == 'etiquette')
		{
			$query .= $field . ' = \'' . $db->escapeString($search) . '\'';
		}
		else
		{
			$query .= $field . ' LIKE \'%' . $db->escapeString($search) . '%\'';
		}

		$query .= ' ORDER BY id DESC;';

		return $db->get($query);
	}

	public function searchSQL($fields, $query)
	{
		$fields = trim($fields);

		if ($fields === '')
		{
			$fields = '*';
		}

		$query = 'SELECT ' . $fields . ' FROM plugin_stock_velos ' . $query;

		$db = DB::getInstance();

		try {
			$statement = $db->prepare($query);
		}
		catch (\Exception $e)
		{
			throw new UserException('Erreur de requête: ' . $e->getMessage());
		}

		if (!$statement->readOnly())
		{
			throw new UserException('Requête invalide : ne doit effectuer que des lectures de données.');
		}

		return $db->get($query);
	}

	public function getSchemaSQL()
	{
		$schema = DB::getInstance()->firstColumn('SELECT sql FROM sqlite_master
			WHERE type = "table" AND name="plugin_stock_velos";');

		$schema = str_replace('CREATE TABLE plugin_stock_velos', 'CREATE TABLE velos', $schema);
		return $schema;
	}


	public function listVelosToSell()
	{
		return DB::getInstance()->get('SELECT * FROM plugin_stock_velos
			WHERE date_sortie IS NULL AND prix > 0 ORDER BY etiquette;');
	}

	public function getStats(string $type, string $period)
	{
		$columns = [];
		$conditions = '1';
		$order = 'period';
		$desc = true;
		$params = [];
		$tables = 'plugin_stock_velos';

		$date_column = $type === 'entry' ? 'date_entree' : 'date_sortie';

		if ($period === 'quarter') {
			$columns['period'] = [
				'label' => 'Trimestre',
				'select' => sprintf('strftime(\'%%Y-T\', %1$s) || CAST((strftime(\'%%m\', %1$s) + 2) / 3 AS string)', $date_column),
				'order' => $date_column . ' %s',
			];

			$group = $columns['period']['select'];
		}
		else {
			$columns['period'] = [
				'label' => 'Année',
				'select' => sprintf('strftime(\'%%Y\', %s)', $date_column),
				'order' => $date_column . ' %s',
			];

			$group = $columns['period']['select'];
		}

		if ($type === 'entry') {
			$columns['group'] = [
				'label' => 'Provenance',
				'select' => 'source',
				'order' => '"period" %s, "group" COLLATE U_NOCASE %1$s',
			];

			$group .= ', source';
		}
		else {
			$columns['group'] = [
				'label' => 'Motif',
				'select' => 'raison_sortie',
				'order' => '"period" %s, "group" COLLATE U_NOCASE %1$s',
			];

			$group .= ', raison_sortie';
		}

		$columns['count'] = [
			'label' => 'Nombre de vélos',
			'select' => 'COUNT(*)',
			'order' => null,
		];

		$columns['weight'] = [
			'label' => 'Poids total',
			'select' => 'SUM(poids)',
			'order' => null,
		];

		$list = new DynamicList($columns, $tables, $conditions);
		$list->groupBy($group);
		$list->orderBy($order, $desc);
		$list->setParameters($params);
		$list->setPageSize(null);

		$current = null;
		$total = 0;
		$total_etp = 0;

		$list->setModifier(function (&$row) use (&$current, &$total, &$total_weight) {
			if ($row->period !== $current) {
				if ($current !== null) {
					yield ['period' => '', 'group' => 'Total', 'count' => $total, 'weight' => $total_weight];
				}

				$current = $row->period;
				$total = 0;
				$total_etp = 0;
				$row->header = true;
			}

			$total += $row->count;
			$total_weight += $row->weight;
		});

		$list->setFinalGenerator(function () use (&$total, &$total_weight) {
			if ($total) {
				yield ['period' => '', 'group' => 'Total', 'count' => $total, 'weight' => $total_weight];
			}
		});

		return $list;
	}

	static public function graphStatsPerYear(): string
	{
		$sql = '
		   SELECT
				strftime(\'%Y\', date_sortie) AS year,
				\'Sortie\' AS type,
				COUNT(*) AS nb
				FROM plugin_stock_velos
				WHERE raison_sortie IS NOT NULL
				GROUP BY strftime(\'%Y\', date_sortie)
			UNION ALL
			SELECT
				strftime(\'%Y\', date_entree) AS year,
				\'Entrée\' AS type,
				COUNT(*) AS nb
				FROM plugin_stock_velos
				GROUP BY strftime(\'%Y\', date_entree)
			ORDER BY year, type;';

		$data = DB::getInstance()->getAssocMulti($sql);
		return self::barGraph(null, $data);
	}

	static public function graphStatsPerExit(): string
	{
		$sql = '
		   SELECT
				strftime(\'%Y\', date_sortie) AS year,
				raison_sortie,
				COUNT(*) AS nb
				FROM plugin_stock_velos
				WHERE raison_sortie IS NOT NULL
				GROUP BY strftime(\'%Y\', date_sortie), raison_sortie ORDER BY year, raison_sortie;
		';

		$data = DB::getInstance()->getAssocMulti($sql);
		return self::barGraph(null, $data);
	}

	static public function graphStatsPerEntry(): string
	{
		$sql = '
		   SELECT
				strftime(\'%Y\', date_entree) AS year,
				source,
				COUNT(*) AS nb
				FROM plugin_stock_velos
				WHERE raison_sortie IS NOT NULL
				GROUP BY strftime(\'%Y\', date_entree), source ORDER BY year, source;
		';

		$data = DB::getInstance()->getAssocMulti($sql);
		return self::barGraph(null, $data);
	}

	static public function barGraph(?string $title, array $data): string
	{
		$bar = new Bar(1000, 400);
		$bar->setTitle($title);

		$color = function (string $str): string {
			return sprintf('#%s', substr(md5($str), 0, 6));
		};

		foreach ($data as $group_label => $group) {
			$set = new Bar_Data_Set($group_label);

			foreach ($group as $label => $value) {
				$set->add($value, $label, $color($label));
			}

			$bar->add($set);
		}

		return $bar->output();
	}
}

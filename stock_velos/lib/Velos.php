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

    protected $db = null;

    protected $max_etiquettes = 1000;

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
        'raison_sortie'  => 'Raison de sortie',
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

    public function getFields(Plugin $plugin): array
    {
        static $exclude_require = ['details_sortie', 'raison_sortie', 'date_sortie'];
        $statuses = (array)($plugin->getConfig('fields') ?? []);
        $defaults = (array)($plugin->getConfig('defaults') ?? []);
        $out = [];

        foreach (self::FIELDS as $name => $label) {
            $status = $statuses[$name] ?? 1;

            $out[$name] = [
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
                $out[$name]['has_options'] = true;
                $out[$name]['options'] = array_combine($o, $o);
            }
        }

        return $out;
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
        ];


        $tables = 'plugin_stock_velos';
        $conditions = 'date_sortie IS NULL';

        $list = new DynamicList($columns, $tables, $conditions);
        $list->orderBy('etiquette', false);
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
                'label' => 'Raison',
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
                'label' => 'poids',
                'export' => true,
            ],
        ];

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

    public function listEtiquettes()
    {
        $etiquettes = array();

        for ($i = 1; $i <= $this->max_etiquettes; $i++)
        {
            $etiquettes[$i] = false;
        }

        $db = DB::getInstance();

        foreach ($db->iterate('SELECT etiquette, prix FROM plugin_stock_velos WHERE date_sortie IS NULL;') as $row)
        {
            $etiquettes[$row->etiquette] = (float) $row->prix;
        }

        return $etiquettes;
    }

    public function getValeurStock()
    {
        return DB::getInstance()->firstColumn('SELECT SUM(prix) FROM plugin_stock_velos WHERE date_sortie IS NULL AND prix > 0;');
    }

    public function getEtiquetteLibre()
    {
        $etiquettes = $this->listEtiquettes();

        foreach ($etiquettes as $num=>$stock)
        {
            if ($stock === false)
                return $num;
        }

        return false;
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

    public function statsByMonth()
    {
        $sql = '
            SELECT
                strftime(\'%Y-%m\', date_sortie) AS month,
                \'Sortie\' AS type,
                raison_sortie AS details,
                COUNT(*) AS nb,
                SUM(poids) AS poids
                FROM plugin_stock_velos
                WHERE raison_sortie IS NOT NULL
                GROUP BY strftime(\'%m/%Y\', date_sortie), raison_sortie
            UNION ALL
            SELECT
                strftime(\'%Y-%m\', date_entree) AS month,
                \'Entrée\' AS type,
                source AS details,
                COUNT(*) AS nb,
                SUM(poids) AS poids
                FROM plugin_stock_velos
                GROUP BY strftime(\'%m/%Y\', date_entree), source
            ORDER BY month DESC, type, details;
        ';
        return DB::getInstance()->get($sql);
    }

    public function statsByYear()
    {
        $sql = '
            SELECT
                strftime(\'%Y\', date_sortie) AS year,
                \'Sortie\' AS type,
                raison_sortie AS details,
                COUNT(*) AS nb,
                SUM(poids) AS poids
                FROM plugin_stock_velos
                WHERE raison_sortie IS NOT NULL
                GROUP BY strftime(\'%Y\', date_sortie), raison_sortie
            UNION ALL
            SELECT
                strftime(\'%Y\', date_entree) AS year,
                \'Entrée\' AS type,
                source AS details,
                COUNT(*) AS nb,
                SUM(poids) AS poids
                FROM plugin_stock_velos
                GROUP BY strftime(\'%Y\', date_entree), source
            ORDER BY year DESC, type, details;
        ';
        return DB::getInstance()->get($sql);
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

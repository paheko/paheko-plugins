<?php

namespace Garradin\Plugin\Stock_Velos;

use Garradin\DB;
use Garradin\Membres;
use Garradin\UserException;
use Garradin\Utils;

class Velos
{
    const A_DEMONTER = -1;

    protected $db = null;

    protected $max_etiquettes = 1000;

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

    protected $types = array(
        'Course',
        'Mi-course',
        'Mini',
        'Pliant',
        'Ville',
        'VTC',
        'VTT',
        'BMX',
        'Autre',
    );

    protected $genres = array(
        'Homme',
        'Femme',
        'Mixte',
        'Enfant/Homme',
        'Enfant/Femme',
        'Enfant/Mixte',
    );

    protected $tailles = array(
        '700C',
        '650B',
        '26"',
        '24"',
        '20"',
        'Autre',
    );

    protected $sources = array(
        'Don',
        'Récupération',
        'Rachat',
    );

    protected $raisons_sortie = array(
        'Démonté',
        'Vendu',
        'Vendu en bourse',
        'Jeté',
    );

    static public function LoopVelos($params, &$return)
    {
        foreach ($params['loopCriterias'] as $criteria)
        {
            if ($criteria['action'] == \KD2\MiniSkel::ACTION_MATCH_FIELD && $criteria['field'] == 'compter')
            {
                $return['query'] = 'SELECT COUNT(*) AS nb_en_vente FROM plugin_stock_velos WHERE prix > 0 AND date_sortie IS NULL;';
                break;
            }
            elseif ($criteria['action'] == \KD2\MiniSkel::ACTION_MATCH_FIELD && $criteria['field'] == 'liste')
            {
                $return['query'] = 'SELECT prix, modele, roues, type, genre, etiquette FROM plugin_stock_velos
                    WHERE date_sortie IS NULL AND prix > 0 ORDER BY date_entree ASC;';
                break;
            }
        }

        return true;
    }

    /**
     * Genres de vélos
     */

    public function listGenres()
    {
        return $this->genres;
    }
    /**
     * Sources de vélo
     */

    public function listSources()
    {
        return $this->sources;
    }

    /**
     * Types de vélos
     */

    public function listTypes()
    {
        return $this->types;
    }

    /**
     * Raisons de sortie
     */

    public function listRaisonsSortie()
    {
        return $this->raisons_sortie;
    }

    /**
     * Tailles des vélos
     */

    public function listTailles()
    {
        return $this->tailles;
    }

    /**
     * Vélos
     */
    public function checkData($data)
    {
        $check_empty = array('etiquette', 'source', 'type', 'genre', 'couleur', 'modele', 'date_entree', 'etat_entree', 'source', 'source_details');

        foreach ($check_empty as $f)
        {
            if (!isset($data[$f]) || !trim($data[$f]))
                throw new UserException("Le champ $f est obligatoire.");
        }

        if (!empty($data['date_sortie']) && $data['date_sortie'] < $data['date_entree'])
        {
            throw new UserException("La date de sortie ne peut pas être antérieure à la date d'entrée.");
        }

        if (!empty($data['date_sortie']) && $data['raison_sortie'] == 'Vendu'
            && (!trim($data['details_sortie']) || !filter_var($data['details_sortie'], FILTER_VALIDATE_INT)))
        {
            throw new UserException("Il est obligatoire de donner le numéro de membre auquel le vélo à été vendu.");
        }

        if (!empty($data['source']) && $data['source'] == 'Rachat')
        {
            $data['source_details'] = (int)$data['source_details'];

            if (empty($data['source_details']))
            {
                throw new UserException("Pour le rachat il est obligatoire de fournir un numéro unique de vélo.");
            }

            $velo = DB::getInstance()->firstColumn('SELECT raison_sortie FROM plugin_stock_velos WHERE id = '.(int)$data['source_details'].';');

            if (!$velo || $velo != 'Vendu')
            {
                throw new UserException("Le vélo indiqué pour le rachat n'existe pas ou n'a pas été vendu.");
            }

        }
    }

    public function addVelo($data)
    {
        if (empty($data['date_entree']) || !Utils::checkDate($data['date_entree']))
        {
            throw new UserException('Date d\'entrée vide ou invalide.');
        }

        if (!empty($data['date_sortie']) && !Utils::checkDate($data['date_sortie']))
        {
            throw new UserException('Date de sortie invalide.');
        }

        if (!isset($data['etiquette']) || !trim($data['etiquette']))
        {
            throw new UserException("Le numéro d'étiquette est obligatoire.");
        }

        if (!isset($data['source']) || !trim($data['source']))
        {
            throw new UserException("La source du vélo est obligatoire.");
        }

        if ($this->getIdFromEtiquette($data['etiquette']))
        {
            throw new UserException("Ce numéro d'étiquette est déjà attribué à un autre vélo en stock.");
        }

        if (!empty($data['source_details']) && is_numeric($data['source_details']))
        {
            $membres = new Membres;

            if (!$membres->get((int)$data['source_details']))
            {
                throw new UserException("Le numéro de membre indiqué comme provenance n'existe pas.");
            }
        }

        if (empty($data['date_sortie']))
        {
            $data['date_sortie'] = null;
            $data['raison_sortie'] = null;
            $data['details_sortie'] = null;
        }

        $db = DB::getInstance();
        $db->insert('plugin_stock_velos', $data);
        return $db->lastInsertRowId();
    }

    public function getVelo($id)
    {
        return DB::getInstance()->first('SELECT * FROM plugin_stock_velos WHERE id = ?;', (int)$id);
    }

    public function editVelo($id, $data)
    {
        if (empty($data['date_entree']) || !Utils::checkDate($data['date_entree']))
        {
            throw new UserException('Date d\'entrée vide ou invalide.');
        }

        if (!empty($data['date_sortie']) && !Utils::checkDate($data['date_sortie']))
        {
            throw new UserException('Date de sortie invalide.');
        }

        if (!isset($data['etiquette']) || !trim($data['etiquette']))
        {
            throw new UserException("Le numéro d'étiquette est obligatoire.");
        }

        if (!isset($data['source']) || !trim($data['source']))
        {
            throw new UserException("La source du vélo est obligatoire.");
        }

        if (($id_e = $this->getIdFromEtiquette($data['etiquette'])) && $id_e != $id && empty($data['date_sortie']))
        {
            throw new UserException("Ce numéro d'étiquette est déjà attribué à un autre vélo en stock.");
        }

        if (!empty($data['source_details']) && is_numeric($data['source_details']))
        {
            $membres = new Membres;

            if (!$membres->get((int)$data['source_details']))
            {
                throw new UserException("Le numéro de membre indiqué comme provenance n'existe pas.");
            }
        }

        if (empty($data['type']))
            $data['type'] = null;

        if (empty($data['genre']))
            $data['genre'] = null;

        if (empty($data['date_sortie']))
        {
            $data['date_sortie'] = null;
            $data['raison_sortie'] = null;
            $data['details_sortie'] = null;
        }

        DB::getInstance()->update('plugin_stock_velos', $data, 'id = '.(int)$id);
        return true;
    }

    public function sortieVelo($id, $raison, $details, $date = null)
    {
        $data = array(
            'raison_sortie' => (string) $raison,
            'details_sortie' => (string) $details,
            'date_sortie' => is_null($date)
                ? gmdate('Y-m-d')
                : (int) $date,
        );

        DB::getInstance()->update('plugin_stock_velos', $data, 'id = '.(int)$id);
        return true;
    }

    public function listVelosStock($order = 'etiquette', $desc = false)
    {
        if (!in_array($order, $this->columns_order))
            $order = 'etiquette';

        return DB::getInstance()->get('SELECT * FROM plugin_stock_velos WHERE date_sortie IS NULL
            ORDER BY '.$order.' COLLATE NOCASE '.($desc ? 'DESC' : 'ASC').';');
    }

    public function listVelosHistorique($order = 'id', $desc = false)
    {
        if (!in_array($order, $this->columns_order))
            $order = 'id';

        return DB::getInstance()->get('SELECT * FROM plugin_stock_velos WHERE date_sortie IS NOT NULL
            ORDER BY '.$order.' COLLATE NOCASE '.($desc ? 'DESC' : 'ASC').';');
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

    public function sellVelo($id, $num_adherent, $prix)
    {
        if (!filter_var($num_adherent, FILTER_VALIDATE_INT))
        {
            throw new UserException('Numéro d\'adhérent non valide.');
        }

        /*
        // Ne pas vérifier si le membre existe vraiment, sinon c'est chiant
        $membres = new Membres;

        if (!$membres->get((int)$num_adherent))
        {
            throw new UserException("Le numéro de membre indiqué ne correspond pas à un membre existant.");
        }
        */

        $data = array(
            'raison_sortie' => 'Vendu',
            'details_sortie' => (int) $num_adherent,
            'date_sortie' => gmdate('Y-m-d'),
            'prix' => (float) $prix
        );

        DB::getInstance()->update('plugin_stock_velos', $data, 'id = '.(int)$id);
        return true;
    }

    public function listVelosToSell()
    {
        return DB::getInstance()->get('SELECT * FROM plugin_stock_velos
            WHERE date_sortie IS NULL AND prix > 0 ORDER BY etiquette;');
    }

    public function checkRachatVelo($id)
    {
        return !DB::getInstance()->firstColumn('SELECT 1 FROM plugin_stock_velos WHERE source_details = '.(int)$id.' AND source = \'Rachat\';');
    }

    public function getRacheteurVelo($id)
    {
        return DB::getInstance()->firstColumn('SELECT details_sortie FROM plugin_stock_velos WHERE id = '.(int)$id.';');
    }

    public function getRachatVelo($id)
    {
        return DB::getInstance()->firstColumn('SELECT id FROM plugin_stock_velos WHERE source_details = '.(int)$id.' AND source = \'Rachat\';');
    }

    public function getMembre($id)
    {
        $membres = new Membres;
        // On stocke le NUMÉRO de membre, et non son ID !
        return $membres->get($membres->getIdWithNumero((int)$id));
    }
}

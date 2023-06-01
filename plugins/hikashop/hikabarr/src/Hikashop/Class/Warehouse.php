<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Class;

use hikashopWarehouseClass;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\WarehouseModel as WarehouseDolibarrModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\WarehouseService;
use Systrio\Plugins\Hikabarr\Hikashop\Models\WarehouseModel;

require_once JPATH_ROOT . '/administrator/components/com_hikashop/helpers/helper.php';
require_once JPATH_ROOT . '/administrator/components/com_hikashop/classes/warehouse.php';

class Warehouse
{

    private DatabaseDriver $db;
    private WarehouseModel $warehouse;
    private Dolibarr $client;

    public function __construct(WarehouseModel $warehouse, Dolibarr $client)
    {
        $this->warehouse = $warehouse;
        $this->client = $client;
        $this->db = Factory::getContainer()->get('DatabaseDriver');
    }

    /**
     * Sauvegarde de l'entrepôt
     */
    public function save(): mixed
    {
        if (!empty($this->get()))
        {
            $this->warehouse->warehouse_id = $this->get()->warehouse_id;
        }

        // Vérification de tous les entrepôts
        $this->verifAll();

        // Sauvegarde
        $hikashopWarehouse = new hikashopWarehouseClass;
        return $hikashopWarehouse->save($this->warehouse);
    }

    /**
     * Suppression d'un entrepôt par rapport à un id
     */
    public function delete(int $id): bool|null
    {
        $id = (empty($id)) ? $this->warehouse->warehouse_id : $id;

        $hikashopWarehouse = new hikashopWarehouseClass;
        return $hikashopWarehouse->delete($id);
    }

    /**
     * Récupération des données d'un entrepôt
     */
    public function get(): mixed
    {
        if (is_null($this->warehouse->warehouse_id) || empty($this->warehouse->warehouse_id))
        {
            return $this->getByName();
        }
        
        return $this->getById();
    }

    /**
     * Récupération des données d'un entrepôt par l'ID
     */
    public function getById(): mixed
    {
        $hikashopWarehouse = new hikashopWarehouseClass;
        return $hikashopWarehouse->get($this->warehouse->warehouse_id);
    }

    /**
     * Récupération des données d'un entrepôt par le nom
     */
    public function getByName(): mixed
    {
        $query = $this->db
            ->getQuery(true)
            ->select('warehouse_id')
            ->from('#__hikashop_warehouse')
            ->where($this->db->qn('warehouse_name') . ' = ' . $this->db->q($this->warehouse->warehouse_name));

        if (!empty($this->db->setQuery($query)->loadObject()->warehouse_id))
        {
            $this->warehouse->warehouse_id = $this->db->setQuery($query)->loadObject()->warehouse_id;
            return $this->getById();
        }

        return false;
    }

    /**
     * Fonction de vérification des entrepôts Hikashop/Dolibarr 
     * les entrepôts d'Hikashop sont contrôlés afin d'avoir les mêmes données que Dolibarr
     * Exemples :
     * Si un entrepôt est ajouté côté Dolibarr on l'ajout à Hikashop
     * Si un entrepôt est supprimé côté Dolibarr on le supprime d'Hikashop
     * Si un entrepôt a changé de nom, on supprime l'entrepôt et on l'ajoute à nouveau
     * 
     * La correspondance des entrepôts se fait par le nom
     */
    public function verifAll()
    {
        // Récupération de tous les entrepôts
        $warehouses_hika = $this->getAll();

        // On boucle
        foreach ($warehouses_hika as $warehouse_hika)
        {
            // Création de l'objet Warehouse Dolibarr
            $warehouse_dolibarr = new WarehouseDolibarrModel;
            $warehouse_dolibarr->label = $warehouse_hika->warehouse_name; // Ajout du nom

            // Appelle du service + récupération de l'entrepôt distant
            $warehouse_dolibarr_service = new WarehouseService($this->client, $warehouse_dolibarr);
            $warehouse = $warehouse_dolibarr_service->get();

            // Si pas de données, on supprime l'entrepôt sur Hikashop
            if (!$warehouse)
            {
                $this->delete($warehouse_hika->warehouse_id);
            }
        }
    }

    /**
     * Récupération de tous les entrepôts d'Hikashop
     */
    public function getAll()
    {
        $query = $this->db
            ->getQuery(true)
            ->select('warehouse_id, warehouse_name')
            ->from('#__hikashop_warehouse');

        return $this->db->setQuery($query)->loadObjectList();
    }
    
}
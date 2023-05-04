<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Hikashop\Class\Warehouse;
use Systrio\Plugins\Hikabarr\Hikashop\Models\WarehouseModel;

defined('_JEXEC') or die;

class WarehouseHelper
{
    private Dolibarr $client;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;        
    }

    public function saveWarehouses(array $warehouses = [])
    {
        // Sauvegarde de tous les entrepôts Dolibarr vers Hikashop
        foreach ($warehouses as $warehouse)
        {
            // Création de l'entrepôt avec le model
            $warehouseHika = new WarehouseModel;
            $warehouseHika->warehouse_name = $warehouse->label;
            $warehouseHika->warehouse_description = $warehouse->description;
            $warehouseHika->warehouse_published = $warehouse->statut;
            
            // Sauvegarde de l'entrepôt
            $warehouseClass = new Warehouse($warehouseHika, $this->client);
            $warehouseClass->save();
        }
    }
    
}
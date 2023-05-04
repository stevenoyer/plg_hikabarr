<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Services;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\OrderModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ProductModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\WarehouseModel;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;

class ProductService
{
    private Dolibarr $client;
    public ProductModel $product;

    public function __construct(Dolibarr $client, ProductModel $product)
    {
        $this->client = $client;
        $this->product = $product;
    }

    public function get()
    {
        // Récupération d'un produit par rapport à sa référence
        $product = $this->client->requestApi->get('products/ref/' . $this->product->ref);
        $product = json_decode($product);

        // Si le résultat détient une erreur
        if (!empty($product->error) && ($product->error->code == 404 || $product->error->code == 400))
        {
            // On récupère le produit par le label
            $product = $this->client->requestApi->get('products/', ['sqlfilters' => '(t.label:=:' . $this->product->label . ')', 'limit' => 1, 'mode' => 1]);
            $product = json_decode($product);
        }

        return $product;
    }

    /**
     * Fonction qui récupère les entrepôts d'un produit
     */
    public function getWarehouses(): bool|array
    {
        // Récupérer les entrepôts par rapport à l'ID de produit
        $warehouses = $this->client->requestApi->get('products/' . $this->product->id . '/stock');
        $warehouses = json_decode($warehouses);

        if (!empty($warehouses->error) && ($warehouses->error->code == 404 || $warehouses->error->code == 401 || $warehouses->error->code == 500))
        {
            return false;
        }

        // Récupération de toutes les données et création d'un tableau
        $warehouses_data = [];
        foreach ($warehouses->stock_warehouses as $key => $item)
		{
            // Création de l'objet
            $warehouseModel = new WarehouseModel;
            $warehouseModel->id = $key;

            // Récupération des données de l'entrepôt
            $warehouseService = new WarehouseService($this->client, $warehouseModel);
            $warehouse_data = $warehouseService->get();

            // Insertion des éléments dans le tableau
            $warehouses_data[] = [
                'label' => $warehouse_data->label,
                'warehouse_id' => $warehouse_data->id,
                'product_id' => $this->product->id,
                'real_stock' => $item->real
            ];
		}

        // Retourne le tableau
        return $warehouses_data;
    }

}
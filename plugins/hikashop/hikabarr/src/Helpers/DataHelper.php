<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ProductModel;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\ProductService;
use Systrio\Plugins\Hikabarr\Hikashop\Class\File;
use Systrio\Plugins\Hikabarr\Hikashop\Models\FileModel;

defined('_JEXEC') or die;

class DataHelper
{
    private Dolibarr $client;
    private CategoryHelper $categoryHelper;
	private ProductHelper $productHelper;
	private WarehouseHelper $warehouseHelper;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;

        $this->categoryHelper = new CategoryHelper($this->client);
		$this->productHelper = new ProductHelper($this->client);
		$this->warehouseHelper = new WarehouseHelper($this->client);
    }
    
    public function fetchDataFromDolibarr()
	{
		// Récupération des données de l'API Dolibarr
		$products = json_decode($this->client->requestApi->get('products'));
		$categories = json_decode($this->client->requestApi->get('categories'));
		$warehouses = json_decode($this->client->requestApi->get('warehouses'));

		// Sauvegarde des données
		$this->categoryHelper->saveCategories($categories);
		$this->productHelper->saveProducts($products);
		$this->warehouseHelper->saveWarehouses($warehouses);
	}
    
}
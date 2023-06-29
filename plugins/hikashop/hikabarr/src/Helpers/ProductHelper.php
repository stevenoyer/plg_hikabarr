<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ProductModel as ProductDolibarrModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\WarehouseModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\ProductService;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\WarehouseService;
use Systrio\Plugins\Hikabarr\Hikashop\Class\Product;
use Systrio\Plugins\Hikabarr\Hikashop\Class\Warehouse;
use Systrio\Plugins\Hikabarr\Hikashop\Models\PriceModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\ProductModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\WarehouseModel as WarehouseModelHikashop;

defined('_JEXEC') or die;

class ProductHelper
{

    private Dolibarr $client;
    private ProductUnitHelper $productUnitHelper;
    private CategoryHelper $categoryHelper;
    private ProductFileHelper $productFileHelper;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;

        $this->productUnitHelper = new ProductUnitHelper;
        $this->categoryHelper = new CategoryHelper($this->client);
        $this->productFileHelper = new ProductFileHelper($this->client);
    }
    
    public function saveProducts(array $products = [])
	{
		// Sauvegarde de tous les produits
		foreach ($products as $product)
		{
			// Instanciation des classes
			$productHika = new ProductModel;
			$priceHika = new PriceModel;

			// Récupération du nom de l'entrepôt
			$warehouse_label_dolibarr = $this->getWarehouseLabelById($product->fk_default_warehouse);

			// On met en place les unités de poids pour l'enregistrement Hikashop
			$weight_units = $this->productUnitHelper->convertWeightUnits($product->weight_units);

			// On met en place les unités de dimension pour l'enregistrement Hikashop
			$width_units = $this->productUnitHelper->convertWidthUnits($product->width_units);
			
			// Données du produit
			$productHika->product_name = $product->label;
			$productHika->product_code = $product->ref;
			$productHika->product_description = $product->description;
			$productHika->product_quantity = (int) $product->stock_reel;
			$productHika->product_weight = (float) $product->weight;
			$productHika->product_weight_unit = $weight_units;
			$productHika->product_width = (float) $product->width;
			$productHika->product_length = (float) $product->length;
			$productHika->product_height = (float) $product->height;
			$productHika->product_dimension_unit = $width_units;
			$productHika->product_warehouse_id = (empty($this->getWarehouseHikashopByName($warehouse_label_dolibarr))) ? (int) $product->fk_default_warehouse : (int) $this->getWarehouseHikashopByName($warehouse_label_dolibarr)->warehouse_id;
			$productHika->product_sort_price = (float) $product->price_ttc;

			// Récupération des catégories Dolibarr
			$categories = $this->categoryHelper->getCategories($product->id);
			$categories_names = $this->categoryHelper->getCategoriesNames($categories);
			$categories_ids = $this->categoryHelper->getCategoriesIds($categories_names);

			// Récupération des images Dolibarr
			$productHika->images = $this->productFileHelper->saveFiles($product->id);
			
			// On défini les catégories auquel l'article est rataché
			$productHika->categories = empty($categories_ids) ? [2] : $categories_ids;
			$priceHika->price_value = (float) $product->price_ttc;
			$priceHika->price_min_quantity = 1;

			// Sauvegarde du produit
			$productClass = new Product($productHika, $priceHika);
			$productClass->save();
		}
	}

	/**
	 * Récupération du label d'un entrepôt par son ID
	 */
	public function getWarehouseLabelById(int $warehouse_id)
	{
		if (!is_null($warehouse_id))
		{
			$warehouseModel = new WarehouseModel;
			$warehouseModel->id = $warehouse_id;
			
			$warehouseService = new WarehouseService($this->client, $warehouseModel);
			$warehouse = $warehouseService->get()->label;
	
			return $warehouse;
		}
	}

	/**
	 * Récupération des informations d'un entrepôt Hikashop par le nom
	 */
	public function getWarehouseHikashopByName(string $warehouse_name)
	{
		if (!is_null($warehouse_name))
		{
			$warehouseModel = new WarehouseModelHikashop;
			$warehouseModel->warehouse_name = $warehouse_name;
	
			$warehouseClass = new Warehouse($warehouseModel, $this->client);
			return $warehouseClass->get();
		}
	}
    
}
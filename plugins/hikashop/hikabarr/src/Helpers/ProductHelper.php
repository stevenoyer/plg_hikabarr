<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Hikashop\Class\Product;
use Systrio\Plugins\Hikabarr\Hikashop\Models\PriceModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\ProductModel;

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
			$productHika->product_warehouse_id = (int) $product->fk_default_warehouse;
			$productHika->product_sort_price = (float) $product->price_ttc;

			// Récupération des catégories Dolibarr
			$categories = $this->categoryHelper->getCategories($product->id);
			$categories_names = $this->categoryHelper->getCategoriesNames($categories);
			$categories_ids = $this->categoryHelper->getCategoriesIds($categories_names);

			// Récupération des images Dolibarr
			$productHika->images = $this->productFileHelper->saveFiles($product->id);
			
			// On défini les catégories auquel l'article est rataché
			$productHika->categories = $categories_ids;
			$priceHika->price_value = (float) $product->price_ttc;

			// Sauvegarde du produit
			$productClass = new Product($productHika, $priceHika);
			$productClass->save();
		}
	}
    
}
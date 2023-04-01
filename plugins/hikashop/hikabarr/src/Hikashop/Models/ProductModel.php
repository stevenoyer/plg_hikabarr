<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Models;

class ProductModel
{
    
    public int|null $product_id = null;
    public string $product_name;
    public string $product_description;
    public int $product_quantity; // Default "-1"
    public int $product_published;
    public string $product_type;
    public float $product_weight;
    public string $product_weight_unit; // Default "kg"
    public float $product_width;
    public float $product_length;
    public float $product_height;
    public string $product_dimension_unit; // Default "m"
    public int $product_warehouse_id;
    public float $product_sort_price;
    public string $product_description_type;
    
    public array $prices;
    public array $categories;

    public function __construct()
    {
        $this->product_type = 'main';
        $this->product_published = 1;
        $this->product_description_type = 'html';
    }

}
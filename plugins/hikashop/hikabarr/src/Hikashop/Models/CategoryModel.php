<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Models;

class CategoryModel
{
    
    public int|null $category_id = null;
    public string $category_name;
    public string $category_type;
    public int $category_parent_id;
    public string $category_description;
    public int $category_published;

    public function __construct()
    {
        $this->category_type = 'product';
        $this->category_published = 1;
        $this->category_parent_id = 2; // Default "product category"
    }

}
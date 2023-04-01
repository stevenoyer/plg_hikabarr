<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Models;

class PriceModel
{
    
    public int $price_currency_id;
    public float $price_value;
    public int $price_min_quantity;

    public function __construct()
    {
        $this->price_currency_id = 1;
        $this->price_min_quantity = 1;
    }

}
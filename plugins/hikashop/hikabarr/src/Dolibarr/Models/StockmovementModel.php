<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Models;

class StockmovementModel
{

    public ?int $product_id;
    public ?int $warehouse_id;
    public ?int $qty;
    public ?string $movementlabel;
    public ?string $movementcode;

}
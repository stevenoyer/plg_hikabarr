<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Models;

class OrderModel
{

    public ?int $id = null;
    public ?int $socid; // Id ThirdParty
    public ?int $date;
    public ?array $lines; // array id product and quantity
    public ?int $idwarehouse = null;
    public ?int $type;
    public ?string $ref_ext; // We use Order Number on Hikashop side

    public function __construct()
    {
        $this->type = 0;
    }

}
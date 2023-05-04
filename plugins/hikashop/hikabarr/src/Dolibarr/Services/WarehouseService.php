<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Services;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\WarehouseModel;

class WarehouseService
{
    private Dolibarr $client;
    public WarehouseModel $warehouse;

    public function __construct(Dolibarr $client, WarehouseModel $warehouse)
    {
        $this->client = $client;
        $this->warehouse = $warehouse;
    }

    public function get()
    {
        $warehouse = $this->client->requestApi->get('warehouses/' . $this->warehouse->id);
        $warehouse = json_decode($warehouse);

        if (!is_array($warehouse))
        {
            return $warehouse;
        }

        foreach ($warehouse as $wh) // $wh = warehouse
        {
            if ($wh->label === $this->warehouse->label)
            {
                return $wh;
            }
        }

        return false;
    }

}
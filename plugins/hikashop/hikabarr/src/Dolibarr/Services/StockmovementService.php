<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Services;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\StockmovementModel;

class StockmovementService
{
    private Dolibarr $client;
    public StockmovementModel $stockmovement;

    public function __construct(Dolibarr $client, StockmovementModel $stockmovement)
    {
        $this->client = $client;
        $this->stockmovement = $stockmovement;
    }

    public function save()
    {
        return $this->client->requestApi->post('stockmovements', json_encode(get_object_vars($this->stockmovement)));
    }

}
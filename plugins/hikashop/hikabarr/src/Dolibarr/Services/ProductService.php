<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Services;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\OrderModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ProductModel;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;

class ProductService
{
    private Dolibarr $client;
    public ProductModel $product;

    public function __construct(Dolibarr $client, ProductModel $product)
    {
        $this->client = $client;
        $this->product = $product;
    }

    public function get()
    {
        $product = $this->client->requestApi->get('products/ref/' . $this->product->ref);
        $product = json_decode($product);

        if (!empty($product->error) && ($product->error->code == 404 || $product->error->code == 400))
        {
            $product = $this->client->requestApi->get('products/', ['sqlfilters' => '(t.label:=:' . $this->product->label . ')', 'limit' => 1, 'mode' => 1]);
            $product = json_decode($product);
        }

        return $product;
    }

}
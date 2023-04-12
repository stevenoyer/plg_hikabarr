<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Class;

use hikashopProductClass;
use Joomla\CMS\Factory;
use Systrio\Plugins\Hikabarr\Hikashop\Models\PriceModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\ProductModel;

class Product
{

    private ProductModel $product;
    private PriceModel $price;

    public function __construct(ProductModel $product, PriceModel $price)
    {
        $this->product = $product;
        $this->price = $price;

        $this->product->prices = [$this->price];
    }

    public function save(): mixed
    {
        if (!empty($this->get()))
        {
            $this->product->product_id = $this->get()->product_id;
        }

        if (!empty($this->getPrices()))
        {
            $this->product->prices = $this->getPrices();
        }

        $hikashopProduct = new hikashopProductClass();
        $success = $hikashopProduct->save($this->product);

        if (!empty($this->product->categories))
        {
            $hikashopProduct->updateCategories($this->product, $success);
        }

        if (!empty($this->product->prices))
        {
            $hikashopProduct->updatePrices($this->product, $success);
        }

        if (!empty($this->product->images))
        {
            $hikashopProduct->updateFiles($this->product, $success);
        }

        return $success;
    }

    public function delete(): bool|null
    {
        $hikashopProduct = new hikashopProductClass;
        return $hikashopProduct->delete($this->get()->product_id);
    }

    public function get(): mixed
    {
        if (is_null($this->product->product_id))
        {
            return $this->getByRef();
        }

        if (is_null($this->product->product_code))
        {
            return $this->getByName();
        }
        
        return $this->getById();
    }

    public function getById(): mixed
    {
        $hikashopProduct = new hikashopProductClass;
        return $hikashopProduct->get($this->product->product_id);
    }

    public function getByName(): mixed
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db
            ->getQuery(true)
            ->select('product_id')
            ->from('#__hikashop_product')
            ->where($db->qn('product_name') . ' = ' . $db->q($this->product->product_name));

        if (!empty($db->setQuery($query)->loadObject()->product_id))
        {
            $this->product->product_id = $db->setQuery($query)->loadObject()->product_id;
            return $this->getById();
        }

        return false;
    }

    public function getByRef(): mixed
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db
            ->getQuery(true)
            ->select('product_id')
            ->from('#__hikashop_product')
            ->where($db->qn('product_code') . ' = ' . $db->q($this->product->product_code));

        if (!empty($db->setQuery($query)->loadObject()->product_id))
        {
            $this->product->product_id = $db->setQuery($query)->loadObject()->product_id;
            return $this->getById();
        }

        return false;
    }

    public function getPrices(): array|bool
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db
            ->getQuery(true)
            ->select('price_id, price_product_id, price_value, price_currency_id')
            ->from('#__hikashop_price')
            ->where($db->qn('price_product_id') . ' = ' . $db->q($this->product->product_id));

        return $db->setQuery($query)->loadObjectList();
    }
    
}
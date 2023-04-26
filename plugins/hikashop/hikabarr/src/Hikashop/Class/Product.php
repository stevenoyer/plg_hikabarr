<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Class;

use hikashopProductClass;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Systrio\Plugins\Hikabarr\Hikashop\Models\PriceModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\ProductModel;

class Product
{

    private ProductModel $product;
    private PriceModel $price;
    private DatabaseDriver $db;

    public function __construct(ProductModel $product, PriceModel $price)
    {
        $this->product = $product;
        $this->price = $price;

        $this->product->prices = [$this->price];
        $this->db = Factory::getContainer()->get('DatabaseDriver');
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
        $query = $this->db
            ->getQuery(true)
            ->select('product_id')
            ->from('#__hikashop_product')
            ->where($this->db->qn('product_name') . ' = ' . $this->db->q($this->product->product_name));

        if (!empty($this->db->setQuery($query)->loadObject()->product_id))
        {
            $this->product->product_id = $this->db->setQuery($query)->loadObject()->product_id;
            return $this->getById();
        }

        return false;
    }

    public function getByRef(): mixed
    {
        $query = $this->db
            ->getQuery(true)
            ->select('product_id')
            ->from('#__hikashop_product')
            ->where($this->db->qn('product_code') . ' = ' . $this->db->q($this->product->product_code));

        if (!empty($this->db->setQuery($query)->loadObject()->product_id))
        {
            $this->product->product_id = $this->db->setQuery($query)->loadObject()->product_id;
            return $this->getById();
        }

        return false;
    }

    public function getPrices(): array|bool
    {
        $query = $this->db
            ->getQuery(true)
            ->select('price_id, price_product_id, price_value, price_currency_id')
            ->from('#__hikashop_price')
            ->where($this->db->qn('price_product_id') . ' = ' . $this->db->q($this->product->product_id));

        return $this->db->setQuery($query)->loadObjectList();
    }
    
}
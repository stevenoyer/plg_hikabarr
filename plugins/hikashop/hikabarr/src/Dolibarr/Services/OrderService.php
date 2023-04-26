<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Services;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\OrderModel;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;

class OrderService
{
    private Dolibarr $client;
    public OrderModel $order;

    public function __construct(Dolibarr $client, OrderModel $order)
    {
        $this->client = $client;
        $this->order = $order;
    }

    public function get()
    {
        $order = $this->client->requestApi->get('orders/' . $this->order->id);
        $order = json_decode($order);

        if (is_array($order))
        {
            $order = $this->client->requestApi->get('orders/ref_ext/' . $this->order->ref_ext);
            $order = json_decode($order);
        }

        return $order;
    }

    public function create()
    {
        return $this->client->requestApi->post('orders', json_encode(get_object_vars($this->order)));
    }

    public function validate()
    {
        return $this->client->requestApi->post('orders/' . $this->get()->id . '/validate');
    }

    public function close()
    {
        return $this->client->requestApi->post('orders/' . $this->get()->id . '/close');
    }

    public function reopen()
    {
        return $this->client->requestApi->post('orders/' . $this->get()->id . '/reopen');
    }

    public function settodraft()
    {
        return $this->client->requestApi->post('orders/' . $this->get()->id . '/settodraft', json_encode(['idwarehouse' => $this->order->idwarehouse]));
    }

    public function createInvoice(bool $paid)
    {
        if ($paid)
        {
            $this->client->requestApi->post('invoices/createfromorder/' . $this->get()->id);
            $this->client->requestApi->post('orders/' . $this->get()->id . '/setinvoiced');
            
            $this->updateInvoice($paid);
        }
    }

    public function updateInvoice(bool $paid)
    {
        foreach ($this->get()->linkedObjectsIds->facture as $invoice)
        {
            if ($paid)
            {
                $this->client->requestApi->post('invoices/' . $invoice . '/settopaid');
            }
        }
    }

    public function getShipmentInfo()
    {
        return $this->client->requestApi->get('orders/' . $this->get()->id . '/shipment');
    }
}
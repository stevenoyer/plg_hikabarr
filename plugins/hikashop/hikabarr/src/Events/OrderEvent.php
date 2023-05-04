<?php 

namespace Systrio\Plugins\Hikabarr\Events;

use Joomla\CMS\Language\Text;
use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\OrderModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ProductModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\StockmovementModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ThirdpartyModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\OrderService;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\ProductService;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\StockmovementService;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\ThirdpartyService;
use Systrio\Plugins\Hikabarr\Helpers\UserHelper;
use Systrio\Plugins\Hikabarr\Hikashop\Class\User;
use Systrio\Plugins\Hikabarr\Hikashop\Models\UserModel;

class OrderEvent
{
    private UserHelper $userHelper;
    private Dolibarr $client;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;
        $this->userHelper = new UserHelper($this->client);
    }

    /**
     * Avant qu'une commande ne soit créée
     * 
     * @link https://www.hikashop.com/support/documentation/62-hikashop-developer-documentation.html#order
     */
    public function onBeforeOrderCreate($order, $do)
    {
        $this->userHelper->updateUserOrder($order);
    }

    /**
     * Avant qu'une commande ne soit mise à jour
     * 
     * @link https://www.hikashop.com/support/documentation/62-hikashop-developer-documentation.html#order
     */
    public function onBeforeOrderUpdate($order, $do)
    {
        $this->userHelper->updateUserOrder($order);
    }

    /**
     * Après qu'une commande est créée
     * 
     * @link https://www.hikashop.com/support/documentation/62-hikashop-developer-documentation.html#order
    */
    public function onAfterOrderCreate(&$order, &$send_email)
    {
        $userModel = new UserModel;
		$userModel->id = $order->order_user_id;

		$user = new User($userModel);

		$userDolibarrModel = new ThirdpartyModel;
		$userDolibarrModel->email = $user->get()->email;

		$userDolibarr = new ThirdpartyService($this->client, $userDolibarrModel);

		$products = [];
		foreach ($order->cart->full_products as $productcart)
		{
			$productModel = new ProductModel;
			$productModel->ref = $productcart->product_code;
			$productModel->label = $productcart->product_name;

			$productDolibarr = new ProductService($this->client, $productModel);
            
			$products[] = [
				'fk_product' => $productDolibarr->get()->id, 
				'qty' => $productcart->cart_product_quantity,
				'tva_tx' => $productDolibarr->get()->tva_tx,
				'subprice' => $productDolibarr->get()->price
			];
		}


		$orderModel = new OrderModel;
		$orderModel->socid = $userDolibarr->get()->id;
		$orderModel->date = $order->cart->cart_modified;
		$orderModel->ref_ext = $order->order_number;
		$orderModel->lines = $products;
		
		$orderService = new OrderService($this->client, $orderModel);
		$orderService->create();

		$this->userHelper->updateUserOrder($order);
    }

    /**
     * Après qu'une commande est mise à jour
     * 
     * @link https://www.hikashop.com/support/documentation/62-hikashop-developer-documentation.html#order
    */
    public function onAfterOrderUpdate(&$order, &$send_email)
    {
        $orderClass = hikashop_get('class.order');
        $order_full = $orderClass->loadFullOrder($order->order_id);

        $orderModel = new OrderModel;
		$orderModel->ref_ext = $order->old->order_number;

		$orderDolibarr = new OrderService($this->client, $orderModel);
		
		if (empty($orderDolibarr->get()->linkedObjectsIds->facture))
		{
            $orderDolibarr->validate();
            
			if ($order->old->order_status == 'created' && $order->order_status == 'confirmed')
			{
                $orderDolibarr->createInvoice(true);

                foreach ($order_full->products as $product)
                {
                    $productModel = new ProductModel;
                    $productModel->ref = $product->order_product_code;
                    $productModel->label = $product->order_product_name;
    
                    $productService = new ProductService($this->client, $productModel);
                    $product_dolibarr = $productService->get();
    
                    $stockmovementModel = new StockmovementModel;
                    $stockmovementModel->product_id = $product_dolibarr->id;
                    $stockmovementModel->warehouse_id = $product_dolibarr->fk_default_warehouse;
                    $stockmovementModel->qty = (int) -$product->order_product_quantity;
                    $stockmovementModel->movementlabel = Text::_('PLG_HIKABARR_STOCKMOVEMENT_LABEL');
                    $stockmovementModel->movementcode = $order->old->order_number;
    
                    $stockmovementService = new StockmovementService($this->client, $stockmovementModel);
                    $stockmovementService->save();
    
                }
			}
		}
    }

}
<?php 

namespace Systrio\Plugins\Hikabarr\Events;

use hikashopOrderClass;
use Joomla\CMS\Factory;
use stdClass;
use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\OrderModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\OrderService;
use Systrio\Plugins\Hikabarr\Helpers\UserHelper;
use Systrio\Plugins\Hikabarr\Hikashop\Class\User;
use Systrio\Plugins\Hikabarr\Hikashop\Models\UserModel;

class UserEvent
{
    private Dolibarr $client;
    private UserHelper $userHelper;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;
        $this->userHelper = new UserHelper($this->client);
    }


    /**
     * Après qu'une commande est mise à jour
     * 
     * @link => https://www.hikashop.com/support/documentation/62-hikashop-developer-documentation.html#product
    */
    public function onUserAccountDisplay(&$buttons) 
    {
        // Récupérer l'utilisateur
        $user_joomla = Factory::getApplication()->getIdentity();

        // Création de l'objet
		$userModel = new UserModel;
		$userModel->id = $user_joomla->id;

        // Appelle de la classe User
		$userClass = new User($userModel);
		
        // On boucle sur toutes les commandes de l'utilisateur avec le statut confirmé
		foreach ($userClass->getOrders('confirmed') as $order)
		{
            // Création de l'objet
			$orderModel = new OrderModel;
			$orderModel->ref_ext = $order->order_number;

            // Appelle du service pour Order
			$orderClass = new OrderService($this->client, $orderModel);
            // Récupération des données d'expéditions
			$shipments = json_decode($orderClass->getShipmentInfo());
			
            // Si $shipments est un tableau
			if (is_array($shipments))
			{
                // On boucle sur les expéditions
				foreach ($shipments as $shipment)
				{
                    // Si le statut = 2 (traité)
					if ($shipment->status == 2)
					{
                        // On créer l'objet
						$orderModelHika = new stdClass;
						$orderModelHika->order_id = $order->order_id;
						$orderModelHika->order_status = 'shipped';

                        // On appelle la fonction hikashop et on sauvegarde le nouveau statut de commande
						$orderHika = new hikashopOrderClass;
						$orderHika->save($orderModelHika);
					}
				}
			}
		}

    }

    /**
	 * Avant qu'un utilisateur soit créé
     * @link => https://www.hikashop.com/support/documentation/62-hikashop-developer-documentation.html#user
	 */
	public function onAfterUserCreate(&$element)
	{
		$this->userHelper->createThirdParty($element);
	}

}
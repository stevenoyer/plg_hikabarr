<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Hikashop\Class\User;
use Systrio\Plugins\Hikabarr\Hikashop\Models\UserModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ThirdpartyModel;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\ThirdpartyService;

defined('_JEXEC') or die;

class UserHelper
{
    private Dolibarr $client;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;
    }
    
    /**
     * Mise à jour de l'utilisateur lors du passage d'une commande
     */
    public function updateUserOrder(object $order)
	{
		// Mise en variable de l'objet UserModel d'Hikashop
		$userData = new UserModel;
		$userData->id = $order->order_user_id;

		// Récupération de l'utilisateur Hikashop
		$user = new User($userData);
		$user_hika = $user->get();

		// Mise à jour de l'utilisateur "tiers" dans Dolibarr
		$userDolibarr = new ThirdpartyModel;
		$userDolibarr->name = $user_hika->name;
		$userDolibarr->email = $user_hika->email;
		$userDolibarr->ref_ext = $user_hika->user_id;
		$userDolibarr->address = $order->cart->shipping_address->address_street;
		$userDolibarr->zip = $order->cart->shipping_address->address_post_code;
		$userDolibarr->town = $order->cart->shipping_address->address_city;
		$userDolibarr->phone = $order->cart->shipping_address->address_telephone;

		// Appelle du service et sauvegarde du "tiers" sur Dolibarr
		$thidPartyClass = new ThirdpartyService($this->client, $userDolibarr);
		$thidPartyClass->save();
	}

	/**
	 * Fonction qui récupère un utilisateur par son ID
	 */
	public function getUserById(int $user_id)
	{
		// Création de l'objet
		$userData = new UserModel;
		$userData->id = $user_id;

		// On retourne les données de l'utilisateur
		return (new User($userData))->get();
	}

	/**
	 * Fonction qui créé un nouveau "Tiers" côté Dolibarr
	 */
    public function createThirdParty(object $element)
    {
        // Mise en variable de l'objet UserModel d'Hikashop
		$userModel = new UserModel;
		$userModel->user_email = $element->user_email;

		// Class utilisateur Hikashop
		$userClass = new User($userModel);

		// Récupération des données utilisateurs Hikashop/Joomla
		$userData = $userClass->get();

		// Création d'un nouveau "tiers" pour Dolibarr
		$userDolibarr = new ThirdpartyModel;
		$userDolibarr->name = $userData->name;
		$userDolibarr->email = $userData->user_email;
		$userDolibarr->ref_ext = $userData->user_id;

		// Appelle du service et sauvegarde du "tiers" sur Dolibarr
		$thidPartyClass = new ThirdpartyService($this->client, $userDolibarr);
		$thidPartyClass->save();
    }
    
}
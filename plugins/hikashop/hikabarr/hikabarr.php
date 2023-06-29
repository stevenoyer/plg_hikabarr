<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Systrio\Plugins\Hikabarr\Events\UserEvent;
use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Events\OrderEvent;
use Systrio\Plugins\Hikabarr\Helpers\DataHelper;
use Systrio\Plugins\Hikabarr\Events\ProductEvent;
use Systrio\Plugins\Hikabarr\Service\HikabarrServiceInterface;

require_once JPATH_PLUGINS . '/hikashop/hikabarr/vendor/autoload.php';

class PlgHikashopHikabarr extends CMSPlugin
{
	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	private DataHelper $dataHelper;
	protected Dolibarr $client;
	private UserEvent $userEvent;
	private OrderEvent $orderEvent;
	protected ProductEvent $productEvent;
	public $params;
	protected string $api_key;
	protected string $api_url;

	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);

		$this->api_key = $this->params->get('api_key');
		$this->api_url = $this->params->get('api_url');

		// Si pas de clé API retourner une erreur
		if (empty($this->api_key))
		{
			return throw new Exception(Text::_('PLG_HIKABARR_ERROR_MISSING_API_KEY'));
		}

		// Si pas d'url de l'api retourner une erreur
		if (empty($this->api_url))
		{
			return throw new Exception(Text::_('PLG_HIKABARR_ERROR_MISSING_API_URL'));
		}

		// Instance des classes
		$this->client = new Dolibarr($this->api_url, $this->api_key);

		// Events
		$this->userEvent = new UserEvent($this->client);
		$this->orderEvent = new OrderEvent($this->client);
		$this->productEvent = new ProductEvent($this->client);

		// Helpers
		$this->dataHelper = new DataHelper($this->client);
	}
	
	public function onBeforeProductListingLoad(&$filters, &$order, &$parent, &$select, &$select2, &$a, &$b, &$on)
	{
		//$this->productEvent->onBeforeProductListingLoad($filters, $order, $parent, $select, $select2, $a, $b, $on);
	}
	
	/**
	 * Avant qu'une commande soit créée
	 */
	public function onAfterOrderCreate(&$order, &$send_email)
	{
		$this->orderEvent->onAfterOrderCreate($order, $send_email);
	}

	/**
	 * Après qu'une commande soit mise à jour
	 */
	public function onAfterOrderUpdate(&$order, &$send_email)
	{
		$this->orderEvent->onAfterOrderUpdate($order, $send_email);
	}

	/**
	 * Avant qu'un utilisateur soit créé
	 */
	public function onAfterUserCreate(&$element)
	{
		$this->userEvent->onAfterUserCreate($element);
	}

	/**
	 * Quand l'utilisateur affiche le tableau de bord
	 */
	public function onUserAccountDisplay(&$buttons)
	{
		$this->userEvent->onUserAccountDisplay($buttons);
	}

	public function fetchData()
	{
		$this->dataHelper->fetchDataFromDolibarr();
	}

	/**
	 * Cron pour récupérer les données de Dolibarr sur Hikashop
	 */
	public function onCronHikabarr(Event $event)
	{
		$this->dataHelper->fetchDataFromDolibarr();
	}

}
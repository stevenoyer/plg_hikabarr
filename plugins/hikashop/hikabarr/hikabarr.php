<?php

use React\Async;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use React\EventLoop\Loop;
use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ThirdpartyModel;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;
use Systrio\Plugins\Hikabarr\Dolibarr\Services\ThirdpartyService;
use Systrio\Plugins\Hikabarr\Helpers\DebugHelper;
use Systrio\Plugins\Hikabarr\Hikashop\Class\File;
use Systrio\Plugins\Hikabarr\Hikashop\Class\User;
use Systrio\Plugins\Hikabarr\Helpers\RequestHelper;
use Systrio\Plugins\Hikabarr\Hikashop\Class\Product;
use Systrio\Plugins\Hikabarr\Hikashop\Class\Category;
use Systrio\Plugins\Hikabarr\Hikashop\Models\FileModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\UserModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\PriceModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\ProductModel;
use Systrio\Plugins\Hikabarr\Hikashop\Models\CategoryModel;

use function React\Async\async;

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
	public $params;
	private DebugHelper $debugHelper;
	private Dolibarr $client;
	private string $api_key;
	private string $api_url;

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
		$this->debugHelper = new DebugHelper;
		$this->client = new Dolibarr($this->api_url, $this->api_key);
	}

	public function saveCategories(array $categories = [])
	{
		// Sauvegarde de toutes les catégories Dolibarr vers Hikashop
		foreach ($categories as $category)
		{
			// Création de la catégorie avec le model
			$categoryHika = new CategoryModel;
			$categoryHika->category_name = $category->label;
			$categoryHika->category_description = $category->description;

			// Sauvegarde de la catégorie
			$categoryClass = new Category($categoryHika);
			$categoryClass->save();
		}
	}

	public function saveProducts(array $products = [])
	{
		// Sauvegarde de tous les produits
		foreach ($products as $product)
		{
			// Instanciation des classes
			$productHika = new ProductModel;
			$priceHika = new PriceModel;

			// On met en place les unités de poids pour l'enregistrement Hikashop
			switch ($product->weight_units) 
			{
				case 0:
					$weight_units = 'kg';
					break;
				case -3:
					$weight_units = 'g';
					break;
				case -6:
					$weight_units = 'mg';
					break;
				
				default:
					$weight_units = 'kg';
					break;
			}

			// On met en place les unités de dimension pour l'enregistrement Hikashop
			switch ($product->width_units) 
			{
				case 0:
					$width_units = 'm';
					break;
				case -1:
					$width_units = 'dm';
					break;
				case -2:
					$width_units = 'cm';
					break;
				case -3:
					$width_units = 'mm';
					break;
				case 99:
					$width_units = 'in';
					break;
				case 98:
					$width_units = 'ft';
					break;
				
				default:
					$width_units = 'cm';
					break;
			}
			
			// Données du produit
			$productHika->product_name = $product->label;
			$productHika->product_code = $product->ref;
			$productHika->product_description = $product->description;
			$productHika->product_quantity = (int) $product->stock_reel;
			$productHika->product_weight = (float) $product->weight;
			$productHika->product_weight_unit = $weight_units;
			$productHika->product_width = (float) $product->width;
			$productHika->product_length = (float) $product->length;
			$productHika->product_height = (float) $product->height;
			$productHika->product_dimension_unit = $width_units;
			$productHika->product_warehouse_id = (int) $product->fk_default_warehouse;
			$productHika->product_sort_price = (float) $product->price_ttc;

			// Récupération des catégories Dolibarr
			$categories = $this->client->requestApi->request(RequestApi::METHOD_GET, 'products/' . $product->id . '/categories');
			$categories = json_decode($categories);

			$categories_names = [];
			$categories_hika = [];
			if (!empty($categories) && is_array($categories))
			{
				// On boucle les catégories de dolibarr et on récupère juste le nom
				foreach ($categories as $category)
				{
					$categories_names[] = $category->label;
				}
			}

			// On boucle sur le nom des catégories afin de récupérer l'ID des catégories côté Hikashop par le nom
			foreach ($categories_names as $name)
			{
				$categoryHika = new CategoryModel;
				$categoryHika->category_name = $name;

				$categoryClass = new Category($categoryHika);
				$categories_hika[] = $categoryClass->get();
			}

			// On récupère seulement les IDs des catégories Hikashop
			$categories_ids = [];
			foreach ($categories_hika as $category)
			{
				$categories_ids[] = $category->category_id;
			}

			// Récupération des images Dolibarr
			$files_dolibarr = $this->client->requestApi->request(RequestApi::METHOD_GET, 'documents', ['modulepart' => 'product', 'id' => $product->id]);
			$files_dolibarr = json_decode($files_dolibarr);
			
			$files_ids = [];
			foreach ($files_dolibarr as $file)
			{
				// On défini un nouveau fichier modèle
				$fileHika = new FileModel;
				$fileHika->file_name = $file->name;
				$fileHika->fullname_dolibarr = $file->fullname;

				// On télécharge les documents du produit
				$download = $this->client->requestApi->request(RequestApi::METHOD_GET, 'documents/download', ['modulepart' => 'product', 'original_file' => explode('/produit/', $file->fullname)[1]]);
				$download = json_decode($download);

				// On instancie la classe File avec le fichier, puis on téléverse le fichier
				$fileClass = new File($fileHika);
				$file_path = $fileClass->upload($download);

				// On défini le chemin du fichier
				$fileHika->file_path = $file_path;

				// Sauvegarde du fichier en base de données + récupération des IDs des fichiers
				$success = $fileClass->save();
				$files_ids[] = $success;
			}

			// On défini les images de l'article
			$productHika->images = $files_ids;
			
			// On défini les catégories auquel l'article est rataché
			$productHika->categories = $categories_ids;
			$priceHika->price_value = (float) $product->price_ttc;

			// Sauvegarde du produit
			$productClass = new Product($productHika, $priceHika);
			$productClass->save();
		}
	}

	public function fetchDataFromDolibarr()
	{
		// Récupération des données de l'API Dolibarr
		$products = json_decode($this->client->requestApi->get('products'));
		$categories = json_decode($this->client->requestApi->get('categories'));

		// Sauvegarde des données
		$this->saveCategories($categories);
		$this->saveProducts($products);
	}
	
	public function onBeforeProductListingLoad(&$filters, &$order, &$parent, &$select, &$select2, &$a, &$b, &$on)
	{
		// Appelle de la fonction de sauvegarde des données en asynchrone
		Loop::addTimer(0, async(function() {
			$this->fetchDataFromDolibarr();
		}));
	}

	
	/**
	 * Avant qu'une commande soit créée
	 */
	public function onBeforeOrderCreate(&$order, &$do)
	{
		$this->updateUserOrder($order);
	}

	/**
	 * Avant qu'un utilisateur soit créé
	 */
	public function onAfterUserCreate(&$element)
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

	public function onBeforeAddressUpdate(&$element, &$do)
	{
		var_dump($element);
	}

	public function onBeforeAddressCreate(&$element, &$do)
	{
		var_dump($element);
	}

	public function onBeforeOrderUpdate(&$order, &$do)
	{
		$this->updateUserOrder($order);
	}

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

}
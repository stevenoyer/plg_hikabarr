<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;
use Systrio\Plugins\Hikabarr\Hikashop\Class\Category;
use Systrio\Plugins\Hikabarr\Hikashop\Models\CategoryModel;

defined('_JEXEC') or die;

class CategoryHelper
{
    private Dolibarr $client;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;        
    }
    
    /**
     * Récupération des catégories par rapport à l'ID d'un produit
     */
    public function getCategories(int $product_id): array
    {
        // Récupération des catégories Dolibarr
        $categories = $this->client->requestApi->request(RequestApi::METHOD_GET, 'products/' . $product_id . '/categories');
        return json_decode($categories);
    }

    /**
     * Récupération des noms des catégories par rapport au tableau de catégories donné
     */
    public function getCategoriesNames(array $categories = []): array
    {
        $categories_names = [];

        if (!empty($categories) && is_array($categories))
        {
            // On boucle les catégories de dolibarr et on récupère juste le nom
            foreach ($categories as $category)
            {
                $categories_names[] = $category->label;
            }
        }

        return $categories_names;
    }

    /**
     * Récupération des ids des catégories par rapport au tableau de nom de catégorie donné
     */
    public function getCategoriesIds(array $categories_names = []): array
    {
        $categories_ids = [];
        $categories_hika = [];

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

        return $categories_ids;
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
    
}
<?php 

namespace Systrio\Plugins\Hikabarr\Events;

use React\EventLoop\Loop;
use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Helpers\DataHelper;
use Systrio\Plugins\Hikabarr\Helpers\UserHelper;

use function React\Async\async;

class ProductEvent
{
    private Dolibarr $client;
    private UserHelper $userHelper;
    private DataHelper $dataHelper;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;

        $this->userHelper = new UserHelper($this->client);
        $this->dataHelper = new DataHelper($this->client);
    }


    /**
     * Après qu'une commande est mise à jour
     * 
     * @link => https://www.hikashop.com/support/documentation/62-hikashop-developer-documentation.html#product
    */
    public function onBeforeProductListingLoad(&$filters, &$order, &$parent, &$select, &$select2, &$a, &$b, &$on) 
    {
        // Appelle de la fonction de sauvegarde des données en "asynchrone"
        // Loop::addTimer(3600, async(function() {
        // }));
        $this->dataHelper->fetchDataFromDolibarr();
    }

}
<?php 

/*-------------- Chargement JOOMLA --------------*/

use Joomla\CMS\Factory;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Plugin\PluginHelper;
use Systrio\Plugins\Hikabarr\Service\HikabarrServiceInterface;

define('_JEXEC', 1);
define('SYSTRIO_PATH_BASE', dirname(__DIR__, 3));

require_once SYSTRIO_PATH_BASE . '/includes/app.php';
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

PluginHelper::importPlugin('hikashop', 'hikabarr');
$dispatcher = Factory::getApplication()->getDispatcher();
$dispatcher->dispatch('onCronHikabarr');
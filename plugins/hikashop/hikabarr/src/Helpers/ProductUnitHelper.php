<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

defined('_JEXEC') or die;

class ProductUnitHelper
{
    /**
     * On converti l'unité de poids Dolibarr pour Hikashop
     */
    public function convertWeightUnits(int $unit)
    {
        $weight_units = 'kg';

        switch ($unit) 
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

        return $weight_units;
    }

    /**
     * On converti l'unité de longueur Dolibarr pour Hikashop
     */
    public function convertWidthUnits(int $unit)
    {
        $width_units = 'cm';

        switch ($unit) 
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

        return $width_units;
    }
    
}
<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

defined('_JEXEC') or die;

class DebugHelper
{
    public function debug(mixed $variable)
    {
        echo '<pre>';
        print_r($variable);
        echo '</pre>';
    }
}
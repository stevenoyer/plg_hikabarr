<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

defined('_JEXEC') or die;

class UrlHelper
{
    
    public function addTrailingSlash(string $url): string
    {
        if (substr($url, -1) != '/')
        {
            $url .= '/';
        }

        return $url;
    }
    
}
<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Helpers;

class CurlHelper
{

    /**
     * @link doc => https://www.php.net/manual/fr/ref.curl.php
     */
    public function curl(string $url, string $method, array $headers = [], array $data = []): string|bool
    {
        $curl = curl_init();

        switch ($method) 
        {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            default:
                $url = sprintf('%s?%s', $url, http_build_query($data));
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

}
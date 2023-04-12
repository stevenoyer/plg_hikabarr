<?php

namespace Systrio\Plugins\Hikabarr\Dolibarr;

use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;

class Dolibarr
{
    public RequestApi $requestApi;
    private string $api_url;
    private string $token;

    public function __construct(string $api_url, string $token)
    {
        $this->requestApi = new RequestApi($api_url, $token);
        $this->api_url = $api_url;
        $this->token = $token;
    }

    public function requestApi()
    {
        return $this->requestApi;
    }
}
<?php

namespace Systrio\Plugins\Hikabarr\Dolibarr;

use Systrio\Plugins\Hikabarr\Dolibarr\Helpers\CurlHelper;
use Systrio\Plugins\Hikabarr\Helpers\UrlHelper;

class RequestApi
{

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    private string $api_url;
    private string $token;

    public function __construct(string $api_url, string $token)
    {
        $urlHelper = new UrlHelper;

        $this->api_url = $urlHelper->addTrailingSlash($api_url);
        $this->token = $token;
    }

    public function request(string $method, $endpoint, $data = []): string|bool
    {
        $curlHelper = new CurlHelper;

        $headers = [
            'Content-Type: application/json', 
            'DOLAPIKEY: ' . $this->token
        ];

        return $curlHelper->curl($this->api_url . $endpoint, $method, $headers, $data);
    }

    public function get(string $endpoint, array|string $data = []): string|bool
    {
        return $this->request(RequestApi::METHOD_GET, $endpoint, $data);
    }

    public function post(string $endpoint, array|string $data = []): string|bool
    {
        return $this->request(RequestApi::METHOD_POST, $endpoint, $data);
    }

    public function put(string $endpoint, array|string $data = []): string|bool
    {
        return $this->request(RequestApi::METHOD_PUT, $endpoint, $data);
    }

    public function delete(string $endpoint, array|string $data = []): string|bool
    {
        return $this->request(RequestApi::METHOD_DELETE, $endpoint, $data);
    }

}
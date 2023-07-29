<?php
namespace rhmdarif\Library;

use Illuminate\Support\Facades\Http;

class HttpRequest {
    private $client;
    private $headers;

    public function __construct($url, $license_key)
    {
        $this->client = Http::baseUrl($url)->withToken($license_key)->withoutVerifying();
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-CLIENT-ORIGIN' => request()->getHost()
        ];
    }

    public function get($url, $params = [], $headers=[]) {
        $this->headers = array_merge($this->headers, $headers);
        return $this->client->withHeaders($this->headers)->get($url, $params);
    }

    public function post($url, $body = [], $headers=[]) {
        $this->headers = array_merge($this->headers, $headers);
        return $this->client->withHeaders($this->headers)->post($url, $body);
    }
}
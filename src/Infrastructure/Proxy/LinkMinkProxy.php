<?php

namespace Infrastructure\Proxy;

use Illuminate\Http\Client\Factory as HttpClient;
use \Illuminate\Http\Client\Response;

final class LinkMinkProxy implements ProxyedBnB
{

    private $response;

    public function __construct(private HttpClient $http)
    {
    }

    public function call(string $endpoint, array $payload = [], string $accessToken = null): void
    {
        $this->response = $this->http
            ->withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(env('LINKMINK_API') . $endpoint, $payload);
    }

    public function response(): Response
    {
        return $this->response;
    }
}

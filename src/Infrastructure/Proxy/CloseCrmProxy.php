<?php

namespace Infrastructure\Proxy;

use Illuminate\Http\Client\Factory as HttpClient;
use \Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

final class CloseCrmProxy implements ProxyedBnB
{

    private $response;

    public function __construct(private HttpClient $http)
    {
    }

    public function call(string $endpoint, array $payload = [], string $accessToken = null): void
    {
        $client = $this->http
            ->withBasicAuth(env('CLOSE_API_KEY'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-tz-offset' => $payload['timezone'],
            ]);

        if (!Str::contains($endpoint, 'lead_')) {
            $this->response = $client->post(env('CLOSE_API') . $endpoint, $payload);
            return;
        }

        $this->response = $client->put(env('CLOSE_API') . $endpoint, $payload);
    }

    public function response(): Response
    {
        return $this->response;
    }
}

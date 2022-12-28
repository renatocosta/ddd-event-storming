<?php

namespace Infrastructure\Proxy;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Illuminate\Contracts\Cache\Repository as Cache;
use Infrastructure\Repositories\CacheUtils;
use Symfony\Component\HttpFoundation\Response as ReponseStatus;

final class ProxyBnBAuthenticator implements ProxyedBnB, Tokenable
{

    private $response;

    public function __construct(private ProxyedBnB $proxyBnB, private HttpClient $http, private Cache $cache)
    {
    }

    public function call(string $endpoint, array $payload = [], string $accessToken = null): void
    {

        $accessToken = $this->cache->get(CacheUtils::SUPPLIER_EXTERNALPARTNER_AUTH_ACCESS_TOKEN, null);

        $this->proxyBnB->call($endpoint, $payload, $accessToken);
        $this->response = $this->proxyBnB->response();

        $this->response->onError(function ($resp)  use ($endpoint, $payload, $accessToken) {
            if ($resp->status() == ReponseStatus::HTTP_UNAUTHORIZED || $resp->status() == ReponseStatus::HTTP_FORBIDDEN || is_null($accessToken)) {
                $accessToken = $this->refreshToken();
            }
            $this->proxyBnB->call($endpoint, $payload, $accessToken);
            $this->response = $this->proxyBnB->response();
        });
    }

    public function refreshToken(): string
    {

        $callAuth = $this->http
            ->withHeaders([
                'Accept' => 'application/json'
            ])->post(env('EXTERNALPARTNER_API') . 'oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => env('EXTERNALPARTNER_CLIENT_ID'),
                'client_secret' => env('EXTERNALPARTNER_CLIENT_SECRET')
            ]);
        $accessToken = $callAuth
            ->throw()
            ->json('access_token');
        $this->cache->put(CacheUtils::SUPPLIER_EXTERNALPARTNER_AUTH_ACCESS_TOKEN, $accessToken);

        return $accessToken;
    }

    public function response(): Response
    {
        return $this->response;
    }
}

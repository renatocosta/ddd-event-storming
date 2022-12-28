<?php

namespace Infrastructure\Proxy;

use \Illuminate\Http\Client\Response;

interface ProxyedBnB
{

    public function call(string $endpoint, array $payload = [], string $accessToken = null): void;

    public function response(): Response;
}

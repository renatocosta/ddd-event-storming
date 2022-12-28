<?php

namespace Infrastructure\Proxy;

interface Tokenable
{

    public function refreshToken(): string;

}

<?php

namespace Infrastructure\Linkmink;

use YlsIdeas\FeatureFlags\Facades\Features;

final class FeaturesFake extends Features
{
    public static function accessible(string $feature): void
    {
    }
}

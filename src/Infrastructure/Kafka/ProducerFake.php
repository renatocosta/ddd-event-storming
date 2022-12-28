<?php

namespace Infrastructure\Kafka;

use Ddd\BnbEnqueueClient\Facades\Producer;

final class ProducerFake extends Producer
{
    public static function sendMessage(string $topic, array $body): void
    {
    }
}

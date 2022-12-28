<?php

namespace Common\ValueObjects\Money;

use Common\ValueObjects\ValueObject;

final class Currency implements ValueObject
{

    private string $value;

    public function __construct(string $value = 'US Dollar')
    {
        $this->value = $value;
    }

    public function asInitial(): string
    {
        return reset(explode(' ', $this->value));
    }

    public function isSame(ValueObject $currency): bool
    {
        return strtolower((string) $this) == strtolower((string) $currency);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Coordinates implements ValueObject
{

    public function __construct(private float $lat, private float $lon)
    {
        try {
            Assert::that($lat)->greaterOrEqualThan(-90.0);
            Assert::that($lat)->lessOrEqualThan(90.0);
            Assert::that($lon)->greaterOrEqualThan(-180.0);
            Assert::that($lon)->lessOrEqualThan(180.0);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }
    }

    public function isSame(ValueObject $coordinates): bool
    {

        if (!$coordinates instanceof self) {
            return false;
        }

        return true;
    }

    public function lat(): float
    {
        return $this->lat;
    }

    public function lon(): float
    {
        return $this->lon;
    }
}

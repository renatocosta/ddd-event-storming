<?php

namespace Common\ValueObjects\Geography;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Zipcode implements ValueObject
{

    public function __construct(private string $value)
    {
        try {
            Assert::that($value)->maxLength(15);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->value = $value;
    }

    public function isSame(ValueObject $zipCode): bool
    {

        if (!$zipCode instanceof self) {
            return false;
        }

        return $this->to() == $zipCode->to();
    }

    public function to(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

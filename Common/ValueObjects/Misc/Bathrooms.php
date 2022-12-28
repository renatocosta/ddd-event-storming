<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Bathrooms implements ValueObject
{

    public function __construct(private string $value)
    {
        try {
            Assert::that($value)->maxLength(30);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->value = $value;
    }

    public function isSame(ValueObject $bathrooms): bool
    {

        if (!$bathrooms instanceof self) {
            return false;
        }

        return $this->to() == $bathrooms->to();
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

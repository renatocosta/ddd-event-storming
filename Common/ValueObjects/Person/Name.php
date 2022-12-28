<?php

namespace Common\ValueObjects\Person;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Name implements ValueObject
{

    public function __construct(private string $value)
    {
        try {
            Assert::that($value)->maxLength(150);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->value = $value;
    }

    public function isSame(ValueObject $name): bool
    {

        if (!$name instanceof self) {
            return false;
        }

        return $this->to() == $name->to();
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

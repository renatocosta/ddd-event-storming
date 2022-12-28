<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Size implements ValueObject
{

    public function __construct(private ?string $value = null)
    {
        try {
            Assert::that($value)->notNull();
            Assert::that((float) $value)->between(0, 99999999.99);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->value = $value;
    }

    public function isSame(ValueObject $bedrooms): bool
    {

        if (!$bedrooms instanceof self) {
            return false;
        }

        return $this->to() == $bedrooms->to();
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

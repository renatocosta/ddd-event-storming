<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Mobile implements ValueObject
{

    public function __construct(private string $number)
    {

        try {
            Assert::that($number)->notEmpty();
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->number = preg_replace('/[^0-9.]+/', '', $number);
    }

    public function isSame(ValueObject $mobile): bool
    {

        if (!$mobile instanceof self) {
            return false;
        }

        return $this->to() == $mobile->to();
    }

    public static function from(array $native): self
    {
        return new self($native['number']);
    }

    public function to(): array
    {
        return ['number' => $this->number];
    }

    public function __toString(): string
    {
        return $this->number;
    }
}

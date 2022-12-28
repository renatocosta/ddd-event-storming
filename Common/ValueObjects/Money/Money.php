<?php

namespace Common\ValueObjects\Money;

use Common\ValueObjects\ValueObject;
use Generator;
use InvalidArgumentException;

final class Money implements ValueObject
{

    protected float $value;

    protected Currency $currency;

    public function __construct(float $value, Currency $currency)
    {
        $this->value = $value;
        $this->currency = $currency;
    }

    public function add(Money $money): Money
    {

        if ($this->currency != $money->currency) {
            throw new InvalidArgumentException("You can only add money with the same currency");
        }

        if ($money->currency->isSame($this->currency)) {
            throw new InvalidArgumentException('Do not provide the same object twice');
        }

        return new Money($this->value + $money->value, $this->currency);
    }

    public function subtract(Money $money): Money
    {

        if ($this->currency != $money->currency) {
            throw new InvalidArgumentException("You can only subtract money with the same currency");
        }

        if ($money->currency->isSame($this->currency)) {
            throw new InvalidArgumentException('Do not provide the same object twice');
        }

        return new Money($this->value - $money->value, $this->currency);
    }

    public function isSame(ValueObject $money): bool
    {

        if (!$money instanceof self) {
            return false;
        }

        return $this->money->value == $money->value && $this->money->currency == $money->currency;
    }

    public static function from($native): self
    {
        return new self($native['value'], $native['currency']);
    }

    public function to(): Generator
    {
        return yield 'value' => $this->value;
        return yield 'currency' => $this->currency;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}

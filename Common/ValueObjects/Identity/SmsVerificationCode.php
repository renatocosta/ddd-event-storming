<?php

namespace Common\ValueObjects\Identity;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class SmsVerificationCode implements ValueObject
{

    private int $code;

    const LENGTH = 6;

    public function __construct(?int $code = null, int $min = 100000, int $max = 999999)
    {
        $this->code = $code ?? random_int($min, $max);
        try {
            Assert::that($this->code)->integer();
            Assert::that((string)$this->code)->length(self::LENGTH);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }
    }

    public function isSame(ValueObject $code): bool
    {

        if (!$code instanceof self) {
            return false;
        }

        return $this->to() == $code->to();
    }

    public static function from(array $native = ['code' => null, 'min' => 100000, 'max' => 999999]): self
    {
        return new self($native['code'], $native['min'], $native['max']);
    }

    public function to(): array
    {
        return ['code' => $this->code];
    }

    public function code(): int
    {
        return $this->code;
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}

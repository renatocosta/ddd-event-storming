<?php

namespace Common\ValueObjects\Identity;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Illuminate\Support\Facades\Hash;
use Common\ValueObjects\ValueObject;

final class Password implements ValueObject
{

    private int $raw;

    private string $hashed;

    public function __construct(int $password, int $length)
    {
        try {
            Assert::that((string)$password)->length($length);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }
        $this->raw = $password;
        $this->hashed = Hash::make($password);
    }

    public function isSame(ValueObject $password): bool
    {

        if (!$password instanceof self) {
            return false;
        }

        return $this->to() == $password->to();
    }

    public static function from(array $native, int $length): self
    {
        return new self($native['password'], $length);
    }

    public function to(): array
    {
        return ['password' => $this->password];
    }

    public function asRaw(): int
    {
        return $this->raw;
    }

    public function __toString(): string
    {
        return $this->hashed;
    }
}

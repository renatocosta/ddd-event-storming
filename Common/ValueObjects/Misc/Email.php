<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Email implements ValueObject
{

    public function __construct(private string $email)
    {
        try {
            Assert::that($email)->email();
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->email = $email;
    }

    public function isSame(ValueObject $email): bool
    {

        if (!$email instanceof self) {
            return false;
        }

        return $this->to() == $email->to();
    }

    public static function from(array $native): self
    {
        return new self($native['email']);
    }

    public function to(): array
    {
        return ['email' => $this->email];
    }

    public function __toString(): string
    {
        return $this->email;
    }
}

<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class HumanCode implements ValueObject
{

    private string $code;

    private const LENGTH = 6;

    public function __construct(string $input = null)
    {
        $inputDefault = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999);
        $code = $input ?? $inputDefault;
        try {
            Assert::that($code)->notEmpty();
            Assert::that($code)->length(self::LENGTH);
            Assert::that($code)->alnum();
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->code = strtoupper($code);
    }

    public function isSame(ValueObject $humanCode): bool
    {

        if (!$humanCode instanceof self) {
            return false;
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}

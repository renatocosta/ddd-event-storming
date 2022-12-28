<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class SmsText implements ValueObject
{

    public const LENGTH = 320;

    public function __construct(private string $text)
    {
        try {
            Assert::that($text)->notEmpty();
            Assert::that($text)->maxLength(self::LENGTH);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->text = $text;
    }

    public function isSame(ValueObject $text): bool
    {

        if (!$text instanceof self) {
            return false;
        }

        return $this->to() == $text->to();
    }

    public static function from(array $native): self
    {
        return new self($native['text']);
    }

    public function to(): array
    {
        return ['text' => $this->text];
    }

    public function __toString(): string
    {
        return $this->text;
    }
}

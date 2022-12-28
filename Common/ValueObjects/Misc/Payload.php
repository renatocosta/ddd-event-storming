<?php

namespace Common\ValueObjects\Misc;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class Payload implements ValueObject
{

    private array $unserialized;

    public function __construct(private string $raw)
    {
        try {
            Assert::that($raw)->notEmpty();
            Assert::that($raw)->isJsonString();
        } catch (AssertionFailedException $e) {
            throw UnableToHandleBusinessRules::dueTo($e->getMessage());
        }

        $this->unserialized = json_decode($this->raw, true);
    }

    public function isSame(ValueObject $raw): bool
    {

        if (!$raw instanceof self) {
            return false;
        }

        return $this->to() == $raw->to();
    }

    public static function from(array $native): self
    {
        return new self(json_encode($native));
    }

    public function to(): array
    {
        return ['raw' => $this->raw];
    }

    public function replaceWith(string $key, $value): self
    {
        $unserialized = $this->asArray();
        $unserialized[$key] = $value;
        return self::from($unserialized);
    }

    public function addWith(string $key, $value): self
    {
        $unserialized = array_merge($this->asArray(), [$key => $value]);
        return self::from($unserialized);
    }

    public function addAList(array $list = []): self
    {
        $this->unserialized = array_replace_recursive($this->asArray(), $list);
        $this->raw = json_encode($this->unserialized);
        return self::from($this->unserialized);
    }

    public function asArray(): array
    {
        return $this->unserialized;
    }

    public function __toString(): string
    {
        return $this->raw;
    }
}

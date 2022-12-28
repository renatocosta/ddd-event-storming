<?php

namespace Common\ValueObjects\DateTime;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;

final class PreferredTime implements ValueObject
{

    public const MORNING = 'morning';

    public const AFTERNOON = 'afternoon';

    public const PERIODS = [self::MORNING => 'morning', self::AFTERNOON => 'afternoon'];

    public function __construct(private string $period)
    {
        try {
            Assertion::keyExists(self::PERIODS, $period);
        } catch (AssertionFailedException $e) {
            throw new UnableToHandleBusinessRules($e->getMessage());
        }
    }

    public function isSame(ValueObject $preferredTime): bool
    {

        if (!$preferredTime instanceof self) {
            return false;
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->period;
    }
}

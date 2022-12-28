<?php

namespace Common\ValueObjects\DateTime;

use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\ValueObject;
use DateTime;
use Exception;

final class DateTimestamp implements ValueObject
{

    private DateTime $date;

    public function __construct(private int $timestamp)
    {
        try {
            $this->date = (new DateTime())->setTimestamp($timestamp);
        } catch (Exception $e) {
            throw new UnableToHandleBusinessRules($e->getMessage());
        }
    }

    public function isSame(ValueObject $dateTimestamp): bool
    {

        if (!$dateTimestamp instanceof self) {
            return false;
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }
}

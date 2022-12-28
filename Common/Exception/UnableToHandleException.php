<?php

namespace Common\Exception;

use DomainException;

abstract class UnableToHandleException extends DomainException
{

    public static function dueTo(string $message, string ...$arguments)
    {
        if (count($arguments) == 0) {
            return new static($message);
        }
        return new static(sprintf($message, ...$arguments));
    }
}

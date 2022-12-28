<?php

namespace Common\ValueObjects\Identity;

use Assert\Assertion;

final class Guid extends Identified
{

    private function __construct(string $id)
    {
        Assertion::uuid($id);
        $this->id = $id;
    }

    public static function from(string $id): Identified
    {
        return new self($id);
    }

    public function __toString(): string
    {
        return $this->id;
    }

}

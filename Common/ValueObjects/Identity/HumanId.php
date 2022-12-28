<?php

namespace Common\ValueObjects\Identity;

final class HumanId extends Identified
{

    private $id;

    private function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function from(int $id): Identified
    {
        return new self($id);
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

}

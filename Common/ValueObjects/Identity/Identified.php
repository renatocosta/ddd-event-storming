<?php

namespace Common\ValueObjects\Identity;

abstract class Identified
{

    public $id;

    /**
     * Tells whether two Identity are equal by comparing their values
     *
     * @param Identified $identifier
     * @return bool
     */
    public function equals(Identified $identifier): bool
    {
        return $this->id == $identifier->id;
    }

    public function getId()
    {
        return $this->id;
    }

    abstract public function __toString(): string;

}

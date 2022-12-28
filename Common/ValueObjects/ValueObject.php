<?php

namespace Common\ValueObjects;

interface ValueObject
{

    public function isSame(ValueObject $object): bool;

}

<?php

namespace Common\ValueObjects;

abstract class Entity
{

    public function isSame(Entity $entity): bool
    {

        if (!$entity instanceof self) {
            return false;
        }

        return true;
    }
}

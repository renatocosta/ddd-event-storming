<?php

namespace Common\Repository;

use Common\ValueObjects\Identity\Identified;

interface Repository
{

    public function create(object $entity): void;

    public function get(Identified $identifier, array $filter = []): object;

    /**
     * @param array $filter
     * @return object[]
     */
    public function getAll(array $filter = [], string $id = null): array;

    public function remove(object $entity): void;

    public function update(object $entity): void;
}

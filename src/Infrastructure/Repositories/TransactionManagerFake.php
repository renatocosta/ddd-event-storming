<?php

namespace Infrastructure\Repositories;

use Closure;
use Illuminate\Database\DatabaseManager;

final class TransactionManagerFake extends DatabaseManager
{

    public function transaction(Closure $closure): void
    {
        $closure();
    }

}

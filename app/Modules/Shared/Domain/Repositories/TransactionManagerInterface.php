<?php

namespace App\Modules\Shared\Domain\Repositories;

interface TransactionManagerInterface
{
    public function run(callable $callback): mixed;
}

<?php

namespace App\Modules\Shared\Infra\Repositories\Persistence\Eloquent;

use App\Modules\Shared\Domain\Repositories\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

class TransactionManager implements TransactionManagerInterface
{
    public function run(callable $callback): mixed
    {
        return DB::transaction(function () use ($callback) {
            return $callback();
        });
    }
}

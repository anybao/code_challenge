<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use App\Models\Repayment;

class RepaymentRepository extends EloquentRepository implements RepositoryInterface
{

    public function getModel()
    {
        return Repayment::class;
    }
}

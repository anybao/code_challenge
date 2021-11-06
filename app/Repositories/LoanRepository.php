<?php

namespace App\Repositories;

use App\Contracts\LoanRepositoryInterface;
use App\Models\Loan;

class LoanRepository extends EloquentRepository implements LoanRepositoryInterface
{
    public function getModel()
    {
        return Loan::class;
    }

    public function getAll()
    {
        return $this->_model->all();
    }
}

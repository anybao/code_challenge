<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    protected $loanRepo;

    public function __construct(LoanRepository $loanRepo)
    {
        $this->loanRepo = $loanRepo;
    }

    public function getLoans()
    {
        $loans = $this->loanRepo->getAll();
        $response = LoanResource::collection($loans);
        return response($response, 200);
    }

    public function submitLoanApplication(Request $request)
    {
        $request->validate([
            'required_amount' => 'required',
            'loan_term' => 'required',
        ]);

        $data = $request->only(['required_amount', 'loan_term']);

        $this->loanRepo->create(array_merge($data, ['user_id' => auth()->id()]));

        return response(null, 201);
    }

    public function approveLoanApplication(Loan $loan)
    {
        $this->loanRepo->update($loan->id, ['status' => 'approved']);
        return response(null, 201);
    }

    public function submitRepayment(Loan $loan, Request $request)
    {
        $request->validate([
            'required_paid_amount' => 'required',
            'actual_paid_amount' => 'required',
        ]);

        // TODO:
    }
}

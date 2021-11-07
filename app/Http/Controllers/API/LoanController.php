<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\LoanResource;
use App\Http\Resources\RepaymentResource;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use App\Repositories\RepaymentRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoanController extends ApiController
{
    protected $loanRepo;
    protected $repaymentRepo;

    /**
     * @param LoanRepository $loanRepo
     */
    public function __construct(LoanRepository $loanRepo, RepaymentRepository $repaymentRepo)
    {
        $this->loanRepo = $loanRepo;
        $this->repaymentRepo = $repaymentRepo;
    }

    /**
     * Get list of current loans
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoans()
    {
        $loans = $this->loanRepo->getAll();
        $response = LoanResource::collection($loans);
        return $this->respond($response, 200, 'Success to get list of loans');
    }

    /**
     * Submit loan application for review
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitLoanApplication(Request $request)
    {
        $request->validate([
            'required_amount' => 'required',
            'loan_term' => 'required',
        ]);

        // Calculate weekly_amount to be paid
        $requiredAmount = data_get($request, 'required_amount');
        $loanTerm = Carbon::make(data_get($request, 'loan_term'));

        if ($loanTerm->isPast()) {
            return $this->respond([], 400, 'Loan term should be a date in future!');
        }

        $totalWeeks = Carbon::now()->diffInWeeks($loanTerm);
        $weeklyPaidAmount = $totalWeeks > 0 ? round($requiredAmount / $totalWeeks) : null;

        $data = $request->only(['required_amount', 'loan_term']);

        $loan = $this->loanRepo->create(array_merge($data, ['user_id' => auth()->id(), 'weekly_paid_amount' => $weeklyPaidAmount]));

        return $this->respond([new LoanResource($loan)], 201, 'Success to submit loan application');
    }

    /**
     * Get the loan detail
     *
     * @param Loan $loan
     * @return \Illuminate\Http\JsonResponse
     */
    public function showLoan(Loan $loan)
    {
        return $this->respond(new LoanResource($loan));
    }

    /**
     * Approve loan application
     *
     * @param Loan $loan
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveLoanApplication(Loan $loan)
    {
        if (auth()->user()->isAdmin()) {
            $this->loanRepo->update($loan->id, ['status' => 'approved']);
            return $this->respond([], 201, 'Success to approve the loan');
        }
        return $this->respond([], 404, 'Permission is denied!');
    }

    /**
     * Submit repayment
     *
     * @param Loan $loan
     * @param Request $request
     */
    public function submitRepayment(Loan $loan, Request $request)
    {
        $request->validate([
            'amount' => 'required',
        ]);
        $submittedAmount = data_get($request, 'amount');

        // check if the loan is approved
        if ($loan->status != 'approved') {
            return $this->respond([], 400, 'The loan has not been approved yet.');
        }

        // check if user submit enough amount
        $weeklyPaidAmount = $loan->weekly_paid_amount;
        if ($submittedAmount < $weeklyPaidAmount) {
            return $this->respond([], 400, 'Required amount for weekly payment is '.$weeklyPaidAmount);
        }

        // check if there are remaining amount
        $remainingAmount = $loan->remain_amount;
        $isPaidCompletely = false;

        if ($remainingAmount > 0 || is_null($remainingAmount)) {
            if ($remainingAmount < $weeklyPaidAmount) {
                // set the loan to be is paid completely
                $isPaidCompletely = true;
            }

            $paidAmount = $loan->paid_amount + $submittedAmount;
            $newRemainingAmount = $loan->required_amount - $paidAmount;
            $this->loanRepo->update($loan->id, [
                'is_paid' => $isPaidCompletely,
                'paid_amount' => $paidAmount,
                'remain_amount' => $newRemainingAmount
            ]);

            // submit new repayment record
            $this->repaymentRepo->create([
                'loan_id' => $loan->id,
                'amount' => $submittedAmount,
                'paid_date' => now(),
            ]);

            return $this->respond([], 200, 'Success to submit repayment.');
        }

        return $this->respond([], 200, 'The loan is paid completely!');
    }

    /**
     * Get list of repayments
     *
     * @param Loan $loan
     */
    public function getRepayments(Loan $loan)
    {
        $repayments = $loan->repayments;

        return $this->respond(RepaymentResource::collection($repayments), 200, 'Success to get list of repayments');
    }
}

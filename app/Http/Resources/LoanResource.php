<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'required_amount' => $this->required_amount,
            'paid_amount' => $this->paid_amount,
            'remain_amount' => $this->remain_amount,
            'loan_term' => $this->loan_term,
            'status' => $this->status,
            'is_paid' => $this->is_paid,
            'weekly_paid_amount' => $this->weekly_paid_amount,
            'created_at' => Carbon::make($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use WithoutMiddleware;
    protected $token;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->user = User::where('email', 'user@gmail.com')->first();
        $this->actingAs($this->user, 'api');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_list_of_loan()
    {
        $response = $this->get('/api/loans', ['Accept' => 'application/json']);

        $response->assertStatus(200);
    }

    public function test_submit_loan_application()
    {
        $data = [
            'required_amount' => 200,
            'loan_term' => '2021-12-20',
        ];

        $response = $this->postJson('/api/loans', $data, ['Accept' => 'application/json']);
        $response->assertStatus(201);
    }

    public function test_fail_to_submit_with_past_date()
    {
        $data = [
            'required_amount' => 200,
            'loan_term' => '2021-09-20',
        ];

        $response = $this->postJson('/api/loans', $data, ['Accept' => 'application/json']);
        $response->assertStatus(400);
    }

    public function test_approve_loan_application()
    {
        $loan = Loan::where('status', 'pending')->first();

        $response = $this->putJson('/api/loans/'.$loan->id.'/approve', [], ['Accept' => 'application/json']);
        if ($this->user->isAdmin()) {
            $response->assertStatus(201);
        } else {
            $response->assertStatus(404);
        }
    }

    public function test_user_able_to_submit_repayment()
    {
        $loan = Loan::where('status', 'approved')->where('remain_amount', '>', 0)->where('is_paid', 0)->first();

        if ($loan) {
            $response = $this->postJson('/api/loans/'.$loan->id.'/repayments', ['amount' => $loan->weekly_paid_amount], ['Accept' => 'application/json']);
            $response->assertStatus(200);
        }
    }
}

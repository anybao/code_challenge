<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {
    Route::namespace('App\Http\Controllers\API')->group(function () {
        Route::get('loans', 'LoanController@getLoans');
        Route::post('loans', 'LoanController@submitLoanApplication');
        Route::get('loans/{loan}', 'LoanController@showLoan');
        Route::put('loans/{loan}/approve', 'LoanController@approveLoanApplication');
        Route::get('loans/{loan}/repayments', 'LoanController@getRepayments');
        Route::post('loans/{loan}/repayments', 'LoanController@submitRepayment');
    });
});

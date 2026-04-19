<?php

use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('deposit', [TransactionController::class, 'deposit']);
Route::post('withdrawal', [TransactionController::class, 'withdrawal']);
Route::get('balance/{wallet_id}', [TransactionController::class, 'balance']);
Route::get('history/{wallet_id}', [TransactionController::class, 'history']);

<?php

use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('deposit', [TransactionController::class, 'deposit']);
Route::post('withdrawal', [TransactionController::class, 'withdrawal']);

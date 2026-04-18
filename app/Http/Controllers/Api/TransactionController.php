<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function deposit(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required',
            'amount' => 'required|decimal:0,2|min:0.01'
        ]);

        $transaction = TransactionService::deposit($request->input('wallet_id'), $request->input('amount'));

        if ($transaction['status']) {
            return $this->success($transaction['data'], $transaction['message']);
        }

        return $this->error($transaction['message'], $transaction['data']);
    }

    public function withdrawal(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required',
            'amount' => 'required|decimal:0,2|min:0.01'
        ]);

        $transaction = TransactionService::withdrawal($request->input('wallet_id'), $request->input('amount'));

        if ($transaction['status']) {
            return $this->success($transaction['data'], $transaction['message']);
        }

        return $this->error($transaction['message'], $transaction['data']);
    }
}

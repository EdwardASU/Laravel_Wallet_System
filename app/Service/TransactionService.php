<?php

namespace App\Service;

use App\Jobs\CalculateRebate;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public static function deposit($wallet_id, $amount)
    {
        DB::beginTransaction();
        try {
            $wallet = Wallet::where('id', $wallet_id)->lockForUpdate()->first();
            if (!$wallet) {
                throw new \Exception('Wallet not found');
            }
            $wallet->update([
                'balance' => $wallet->balance + $amount,
            ]);

            $transaction = Transaction::create([
                'wallet_id' => $wallet_id,
                'type' => 'deposit',
                'amount' => $amount,
            ]);

            DB::commit();

            CalculateRebate::dispatch($transaction);

            $data = [
                'status' => true,
                'message' => 'Deposit successful',
                'data' => $wallet,
            ];

            return $data;
        } catch (\Exception $e) {
            DB::rollBack();

            $data = [
                'status' => false,
                'message' => 'Deposit failed',
                'data' => $e->getMessage(),
            ];

            return $data;
        }
    }

    public static function withdrawal($wallet_id, $amount)
    {
        DB::beginTransaction();
        try {
            $wallet = Wallet::where('id', $wallet_id)->lockForUpdate()->first();
            if (!$wallet) {
                throw new \Exception('Wallet not found');
            }
            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient balance');
            }

            $wallet->update([
                'balance' => $wallet->balance - $amount,
            ]);

            $transaction = Transaction::create([
                'wallet_id' => $wallet_id,
                'type' => 'withdrawal',
                'amount' => $amount,
            ]);

            DB::commit();

            $data = [
                'status' => true,
                'message' => 'withdrawal successful',
                'data' => $wallet,
            ];

            return $data;
        } catch (\Exception $e) {
            DB::rollBack();

            $data = [
                'status' => false,
                'message' => 'Withdrawal failed',
                'data' => $e->getMessage(),
            ];

            return $data;
        }
    }

    public static function balance($wallet_id)
    {
        $wallet = Wallet::find($wallet_id);

        if (!$wallet) {
            return [
                'status' => false,
                'message' => 'Wallet not found',
                'data' => null,
            ];
        }

        return [
            'status' => true,
            'message' => 'Wallet balance retrieved',
            'data' => $wallet->balance,
        ];
    }

    public static function history($wallet_id)
    {
        $wallet = Wallet::find($wallet_id);

        if (!$wallet) {
            return [
                'status' => false,
                'message' => 'Wallet not found',
                'data' => null,
            ];
        }

        return [
            'status' => true,
            'message' => 'Transaction history retrieved',
            'data' => $wallet->transactions()->latest()->get(),
        ];
    }
}

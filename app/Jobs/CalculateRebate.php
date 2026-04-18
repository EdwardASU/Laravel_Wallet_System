<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CalculateRebate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    private Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            $wallet = Wallet::where('id', $this->transaction->wallet_id)->lockForUpdate()->first();
            $amount = $this->transaction->amount * 0.01;

            $wallet->update([
                'balance' => $wallet->balance + $amount,
            ]);

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'rebate',
                'amount' => $amount,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error($e->getMessage());
        }
    }
}

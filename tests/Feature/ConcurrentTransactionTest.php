<?php

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Process;

uses(DatabaseTruncation::class);

it('handles concurrent deposits accurately without lost updates', function () {
    $wallet = Wallet::factory()->create([
        'balance' => 1000,
    ]);

    $times = 5;

    // We build a pool of concurrent processes
    $pool = Process::pool(function ($pool) use ($wallet, $times) {
        for ($i = 0; $i < $times; $i++) {
            // Using tinker to execute the PHP code
            $code = "App\Service\TransactionService::deposit({$wallet->id}, 100);";
            // Use array arguments and --env=testing to avoid shell escaping issues and use testing DB
            $pool->path(base_path())->command(['php', 'artisan', 'tinker', '--env=testing', '--execute', $code]);
        }
    });

    // Run the processes concurrently
    $pool->start()->wait();

    $wallet->refresh();

    // 1000 + (100 * 5) + (1 * 5) = 1505
    expect((float) $wallet->balance)->toBe(1505.0);

    expect(Transaction::where('wallet_id', $wallet->id)->where('type', 'deposit')->count())->toBe(5);
    expect(Transaction::where('wallet_id', $wallet->id)->where('type', 'rebate')->count())->toBe(5);
});

it('handles concurrent withdrawals accurately without negative balance', function () {
    $wallet = Wallet::factory()->create([
        'balance' => 100,
    ]);

    $times = 5;

    // 5 concurrent requests to withdraw 100
    // Only 1 should succeed, 4 should fail
    $pool = Process::pool(function ($pool) use ($wallet, $times) {
        for ($i = 0; $i < $times; $i++) {
            $code = "App\Service\TransactionService::withdrawal({$wallet->id}, 100);";
            $pool->path(base_path())->command(['php', 'artisan', 'tinker', '--env=testing', '--execute', $code]);
        }
    });

    $pool->start()->wait();

    $wallet->refresh();

    // The balance should not be negative
    expect((float) $wallet->balance)->toBe(0.0);

    expect(Transaction::where('wallet_id', $wallet->id)->where('type', 'withdrawal')->count())->toBe(1);
});

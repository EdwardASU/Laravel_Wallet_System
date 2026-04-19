<?php

use App\Models\Transaction;
use App\Models\Wallet;
use App\Service\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Jobs\CalculateRebate;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('calculates and credits rebate correctly on deposit', function () {
    Queue::fake();

    $wallet = Wallet::factory()->create([
        'balance' => 1000,
    ]);

    $this->postJson('/api/deposit', [
        'wallet_id' => $wallet->id,
        'amount' => 100,
    ])->assertSuccessful()
        ->assertJson([
            'message' => 'Deposit successful',
        ]);

    // Assert deposit transaction exists
    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $wallet->id,
        'type' => 'deposit',
        'amount' => 100,
    ]);

    // Verify job was pushed
    Queue::assertPushed(CalculateRebate::class, function ($job) use ($wallet) {
        return $job->transaction->wallet_id === $wallet->id;
    });

    // Manually run the job to test its side effects
    $transaction = Transaction::where('wallet_id', $wallet->id)->where('type', 'deposit')->first();
    (new CalculateRebate($transaction))->handle();

    $wallet->refresh();

    // 1000 + 100 (deposit) + 1 (1% rebate) = 1101
    expect((float) $wallet->balance)->toBe(1101.0);

    // Assert rebate transaction exists
    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $wallet->id,
        'type' => 'rebate',
        'amount' => 1,
    ]);
});

it('processes successful withdrawal and updates balance', function () {
    $wallet = Wallet::factory()->create([
        'balance' => 1000,
    ]);

    $this->postJson('/api/withdrawal', [
        'wallet_id' => $wallet->id,
        'amount' => 500,
    ])->assertSuccessful()
        ->assertJson([
            'message' => 'withdrawal successful',
        ]);

    $wallet->refresh();

    expect((float) $wallet->balance)->toBe(500.0);

    // Assert withdrawal transaction exists
    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $wallet->id,
        'type' => 'withdrawal',
        'amount' => 500,
    ]);
});

it('fails withdrawal when insufficient balance', function () {
    $wallet = Wallet::factory()->create([
        'balance' => 100,
    ]);

    $this->postJson('/api/withdrawal', [
        'wallet_id' => $wallet->id,
        'amount' => 500,
    ])->assertStatus(400) // Controller returns error(), let's check what it returns
        ->assertJson([
            'message' => 'Withdrawl failed',
            'data' => 'Insufficient balance',
        ]);

    $wallet->refresh();

    // Balance should remain the same
    expect((float) $wallet->balance)->toBe(100.0);

    // No withdrawal transaction should be created
    $this->assertDatabaseMissing('transactions', [
        'wallet_id' => $wallet->id,
        'type' => 'withdrawal',
    ]);
});

it('retrieves the current wallet balance via API', function () {
    $wallet = Wallet::factory()->create(['balance' => 1234.56]);

    $this->getJson("/api/balance/{$wallet->id}")
        ->assertSuccessful()
        ->assertJson([
            'message' => 'Wallet balance retrieved',
            'data' => 1234.56,
        ]);
});

it('retrieves the transaction history via API', function () {
    $wallet = Wallet::factory()->create();

    // Create some transactions
    Transaction::create(['wallet_id' => $wallet->id, 'type' => 'deposit', 'amount' => 100]);
    Transaction::create(['wallet_id' => $wallet->id, 'type' => 'withdrawal', 'amount' => 50]);

    $this->getJson("/api/history/{$wallet->id}")
        ->assertSuccessful()
        ->assertJson([
            'message' => 'Transaction history retrieved',
        ])
        ->assertJsonCount(2, 'data');
});

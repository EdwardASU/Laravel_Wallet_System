# Wallet Concurrency Handling: A Simple Guide

## Issues

If not doing Concurrency Handling, we will facing data corruption issues.
For the example,
We have a wallet with balance $100.
We have 2 requests coming in at the same time, both try to withdraw $80.
Without Concurrency Handling, both requests will read the balance as $100.
Both requests will calculate: $100 - $80 = $20.
Both requests will write $20 to the database.
The result is: The user withdrew $160, but the balance is $20. This is incorrect.

## The Fix

1. To prevent this, we use Laravel's **lockForUpdate()** (Pessimistic Locking). It will **lock the row** to prevent other requests from modifying it.

- When the first request comes in to modify a wallet, it immediately **locks** that wallet.
- If a second request wants to modify the same wallet, it has to **wait in line**.
- Once the first request finishes its business (saves and commits the transaction), the second request is allowed in.
- The second request now sees the **updated balance** from the first request and performs its calculation correctly.

This ensures that no matter how many people are clicking buttons at once, the balance remains 100% accurate.

2. Using **DB::beginTransaction()**, **DB::commit()** and **DB::rollBack()** to wrap the transaction. If the transaction fails, it will be rolled back and the data wont be written into the database causing data corruption.

## Future Improvement

1. **Absolute Precision**: Using **BCMath** (BCmult, BCadd, Bcsub) to avoid floating point precision issues. For financial apps or some system that require high precision calculation, float point math can sometimes introduce tiny rounding errors over millions of transactions.
2. **Faster Updates**: Using Laravel's **increment()** method. This allows the database itself to handle the addition, which is faster, more elegant, and requires less code.
3. **Double-Click Protection**: Implementing **"Idempotency"**. By giving each request a unique ID, we can automatically reject duplicate clicks, saving server resources and preventing duplicate charges. And it also can helping calculate rebate preventing multiple rebate for a single deposit.
4. **Job Retry on Failure**: Adding retry logic to the job. If the job fails, it will retry again.

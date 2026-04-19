# Wallet System

A digital wallet system built with Laravel. It can handling deposits and withdrawals, and automatically calculate rebates. It is safe for high concurrency environments.

## Features

- **Deposit & Withdrawal**: Basic wallet operations with balance check preventing overdraft.
- **Auto Rebate**: Every deposit will trigger a background job to calculate 1% rebate into the wallet.
- **Concurrency Handling**: Using **lockForUpdate()** and database transactions to prevent data corruption.
- **History & Balance**: API endpoints to check current balance and transaction history.
- **Unit Testing**: Including concurrency testing using Laravel Process Pool.

## Requirements

- **PHP**: 8.3+
- **Database**: MySQL or SQLite (Need `pdo_sqlite` for testing)

## Setup

1. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Environment configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database setup**:
   Configure `.env` file, then run:
   ```bash
   # Create tables and generate a test wallet (id: 1, balance: 1000)
   php artisan migrate:fresh --seed
   ```

4. **Queue worker**:
   Start worker to processing rebate jobs:
   ```bash
   php artisan queue:work
   ```

## API

Import `laravel-wallet.postman_collection.json` into Postman for testing.

- **POST /api/deposit**: Deposit funds.
- **POST /api/withdrawal**: Withdraw funds.
- **GET /api/balance/{id}**: Get wallet balance.
- **GET /api/history/{id}**: Get transaction history.

## Testing

Run tests to verify logic and concurrency:

```bash
php artisan test
```

> Note: For concurrency test, it is recommended to using a real database like MySQL. For SQLite, ensure it is enabled in your PHP environment.

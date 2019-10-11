<?php
declare(strict_types=1);

use App\Services\FireflyIIIApi\Model\AccountType;
use App\Services\FireflyIIIApi\Model\TransactionType;

return [
    // having the source + dest will tell you the transaction type.
    'account_to_transaction'    => [
        AccountType::ASSET           => [
            AccountType::ASSET           => TransactionType::TRANSFER,
            AccountType::CASH            => TransactionType::WITHDRAWAL,
            AccountType::DEBT            => TransactionType::WITHDRAWAL,
            AccountType::EXPENSE         => TransactionType::WITHDRAWAL,
            AccountType::INITIAL_BALANCE => TransactionType::OPENING_BALANCE,
            AccountType::LOAN            => TransactionType::WITHDRAWAL,
            AccountType::MORTGAGE        => TransactionType::WITHDRAWAL,
            AccountType::RECONCILIATION  => TransactionType::RECONCILIATION,
        ],
        AccountType::CASH            => [
            AccountType::ASSET => TransactionType::DEPOSIT,
        ],
        AccountType::DEBT            => [
            AccountType::ASSET           => TransactionType::DEPOSIT,
            AccountType::DEBT            => TransactionType::TRANSFER,
            AccountType::EXPENSE         => TransactionType::WITHDRAWAL,
            AccountType::INITIAL_BALANCE => TransactionType::OPENING_BALANCE,
            AccountType::LOAN            => TransactionType::TRANSFER,
            AccountType::MORTGAGE        => TransactionType::TRANSFER,
        ],
        AccountType::INITIAL_BALANCE => [
            AccountType::ASSET    => TransactionType::OPENING_BALANCE,
            AccountType::DEBT     => TransactionType::OPENING_BALANCE,
            AccountType::LOAN     => TransactionType::OPENING_BALANCE,
            AccountType::MORTGAGE => TransactionType::OPENING_BALANCE,
        ],
        AccountType::LOAN            => [
            AccountType::ASSET           => TransactionType::DEPOSIT,
            AccountType::DEBT            => TransactionType::TRANSFER,
            AccountType::EXPENSE         => TransactionType::WITHDRAWAL,
            AccountType::INITIAL_BALANCE => TransactionType::OPENING_BALANCE,
            AccountType::LOAN            => TransactionType::TRANSFER,
            AccountType::MORTGAGE        => TransactionType::TRANSFER,
        ],
        AccountType::MORTGAGE        => [
            AccountType::ASSET           => TransactionType::DEPOSIT,
            AccountType::DEBT            => TransactionType::TRANSFER,
            AccountType::EXPENSE         => TransactionType::WITHDRAWAL,
            AccountType::INITIAL_BALANCE => TransactionType::OPENING_BALANCE,
            AccountType::LOAN            => TransactionType::TRANSFER,
            AccountType::MORTGAGE        => TransactionType::TRANSFER,
        ],
        AccountType::RECONCILIATION  => [
            AccountType::ASSET => TransactionType::RECONCILIATION,
        ],
        AccountType::REVENUE         => [
            AccountType::ASSET    => TransactionType::DEPOSIT,
            AccountType::DEBT     => TransactionType::DEPOSIT,
            AccountType::LOAN     => TransactionType::DEPOSIT,
            AccountType::MORTGAGE => TransactionType::DEPOSIT,
        ],
    ],
];

<?php
declare(strict_types=1);
/**
 * Accounts.php
 * Copyright (c) 2020 james@firefly-iii.org
 *
 * This file is part of the Firefly III CSV importer
 * (https://github.com/firefly-iii/csv-importer).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Import\Task;

use GrumpyDictator\FFIIIApiSupport\Model\Account;
use Log;

/**
 * Class Accounts
 */
class Accounts extends AbstractTask
{

    /**
     * @param array $group
     *
     * @throws \App\Exceptions\ApiHttpException
     * @return array
     */
    public function process(array $group): array
    {
        Log::debug('Now in Accounts::process()');
        $total = count($group['transactions']);
        foreach ($group['transactions'] as $index => $transaction) {
            Log::debug(sprintf('Now processing transaction %d of %d', $index + 1, $total));
            $group['transactions'][$index] = $this->processTransaction($transaction);
        }

        return $group;
    }

    /**
     * Returns true if the task requires the default account.
     *
     * @return bool
     */
    public function requiresDefaultAccount(): bool
    {
        return true;
    }

    /**
     * Returns true if the task requires the default currency of the user.
     *
     * @return bool
     */
    public function requiresTransactionCurrency(): bool
    {
        return false;
    }

    /**
     * @param array        $array
     *
     * @param Account|null $defaultAccount
     *
     * @return array
     */
    private function findAccount(array $array, ?Account $defaultAccount): array
    {
        Log::debug('Now in findAccount', $array);
        if (null === $defaultAccount) {
            Log::debug('findAccount() default account is NULL.');
        }
        if (null !== $defaultAccount) {
            Log::debug(sprintf('Default account is #%d ("%s")', $defaultAccount->id, $defaultAccount->name));
        }

        Log::debug('Found no account or haven\'t searched for one.');

        // append an empty type to the array for consistency's sake.
        $array['type'] = $array['type'] ?? null;
        $array['bic']  = $array['bic'] ?? null;

        // Return ID or name if not null
        if (null !== $array['id'] || null !== $array['name']) {
            Log::debug('Array with account has some info, return that.', $array);

            return $array;
        }

        // if the default account is not NULL, return that one instead:
        if (null !== $defaultAccount) {
            $default = $defaultAccount->toArray();
            Log::debug('Default account is not null, so will return:', $default);

            return $default;
        }
        Log::debug('Default account is NULL, so will return: ', $array);

        return $array;
    }

    /**
     * @param array $transaction
     *
     * @return array
     */
    private function getDestinationArray(array $transaction): array
    {
        return [
            'transaction_type' => $transaction['type'],
            'id'               => $transaction['destination_id'],
            'name'             => $transaction['destination_name'],
            'iban'             => $transaction['destination_iban'] ?? null,
            'number'           => $transaction['destination_number'] ?? null,
            'bic'              => $transaction['destination_bic'] ?? null,
        ];
    }

    /**
     * @param array $transaction
     *
     * @return array
     */
    private function getSourceArray(array $transaction): array
    {
        return [
            'transaction_type' => $transaction['type'],
            'id'               => $transaction['source_id'],
            'name'             => $transaction['source_name'],
            'iban'             => $transaction['source_iban'] ?? null,
            'number'           => $transaction['source_number'] ?? null,
            'bic'              => $transaction['source_bic'] ?? null,
        ];
    }

    /**
     * @param array $transaction
     * @param array $source
     *
     * @return array
     */
    private function setSource(array $transaction, array $source): array
    {
        return $this->setTransactionAccount('source', $transaction, $source);
    }

    /**
     * @param array $transaction
     * @param array $source
     *
     * @return array
     */
    private function setDestination(array $transaction, array $source): array
    {
        return $this->setTransactionAccount('destination', $transaction, $source);
    }

    /**
     * @param string $direction
     * @param array  $transaction
     * @param array  $account
     *
     * @return array
     */
    private function setTransactionAccount(string $direction, array $transaction, array $account): array
    {
        $transaction[sprintf('%s_id', $direction)]     = $account['id'];
        $transaction[sprintf('%s_name', $direction)]   = $account['name'];
        $transaction[sprintf('%s_iban', $direction)]   = $account['iban'];
        $transaction[sprintf('%s_number', $direction)] = $account['number'];
        $transaction[sprintf('%s_bic', $direction)]    = $account['bic'];

        return $transaction;
    }

    /**
     * // TODO add warning when falling back on the default account.
     *
     * @param array $transaction
     *
     * @return array
     */
    private function processTransaction(array $transaction): array
    {
        Log::debug('Now in Accounts::processTransaction()');

        /*
         * Try to find the source and destination accounts in the transaction.
         *
         * The source account will default back to the user's submitted default account.
         * So when everything fails, the transaction will be a deposit for amount X.
         */
        $sourceArray = $this->getSourceArray($transaction);
        $destArray   = $this->getDestinationArray($transaction);
        $source      = $this->findAccount($sourceArray, $this->account);
        $destination = $this->findAccount($destArray, null);

        /*
         * First, set source and destination in the transaction array:
         */
        $transaction         = $this->setSource($transaction, $source);
        $transaction         = $this->setDestination($transaction, $destination);
        $transaction['type'] = $this->determineType($source['type'], $destination['type']);

        $amount = (string) $transaction['amount'];
        $amount = '' === $amount ? '0' : $amount;

        if('0'===$amount) {
            Log::error('Amount is ZERO. This will give trouble further down the line.');
        }

        /*
         * If the amount is positive, the transaction is a deposit. We switch Source
         * and Destination and see if we can still handle the transaction:
         */
        if (1 === bccomp($amount, '0')) {
            // amount is positive
            Log::debug(sprintf('%s is positive.', $amount));
            $transaction         = $this->setSource($transaction, $destination);
            $transaction         = $this->setDestination($transaction, $source);
            $transaction['type'] = $this->determineType($destination['type'], $source['type']);
        }

        /*
         * Final check. If the type is "withdrawal" but the destination account found is "revenue"
         * we found the wrong one. Just submit the name and hope for the best.
         */
        if ('revenue' === $destination['type'] && 'withdrawal' === $transaction['type']) {
            Log::warning('The found destination account is of type revenue but this is a withdrawal. Out of cheese error.');
            $transaction['destination_id']   = null;
            $transaction['destination_name'] = $destination['name'];
            $transaction['destination_iban'] = $destination['iban'];
        }

        /*
         * Same but for the other way around.
         * If type is "deposit" but the source account is an expense account.
         * Submit just the name.
         */
        if ('expense' === $source['type'] && 'deposit' === $transaction['type']) {
            Log::warning('The found source account is of type expense but this is a deposit. Out of cheese error.');
            $transaction['source_id']   = null;
            $transaction['source_name'] = $source['name'];
            $transaction['source_iban'] = $source['iban'];
        }

        /*
         * if new source or destination ID is filled in, drop the other fields:
         */
        if (0 !== $transaction['source_id'] && null !== $transaction['source_id']) {
            $transaction['source_name']   = null;
            $transaction['source_iban']   = null;
            $transaction['source_number'] = null;
        }
        if (0 !== $transaction['destination_id'] && null !== $transaction['destination_id']) {
            $transaction['destination_name']   = null;
            $transaction['destination_iban']   = null;
            $transaction['destination_number'] = null;
        }

        return $transaction;
    }


    /**
     * @param string|null $sourceType
     * @param string|null $destinationType
     *
     * @return string
     */
    private function determineType(?string $sourceType, ?string $destinationType): string
    {
        Log::debug(sprintf('Now in determineType("%s", "%s")', $sourceType, $destinationType));
        if (null === $sourceType && null === $destinationType) {
            Log::debug('Return withdrawal, both are NULL');
            return 'withdrawal';
        }

        // if source is a asset and dest is NULL, its a withdrawal
        if ('asset' === $sourceType && null === $destinationType) {
            Log::debug('Return withdrawal, source is asset');
            return 'withdrawal';
        }
        // if destination is asset and source is NULL, its a deposit
        if(null === $sourceType && 'asset' === $destinationType){
            Log::debug('Return deposit, dest is asset');
            return 'deposit';
        }

        $key   = sprintf('transaction_types.account_to_transaction.%s.%s', $sourceType, $destinationType);
        $type  = config($key);
        $value = $type ?? 'withdrawal';
        Log::debug(sprintf('Check config for "%s" and found "%s". Returning "%s"', $key, $type, $value));

        return $value;
    }
}

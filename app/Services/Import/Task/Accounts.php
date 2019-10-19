<?php
/**
 * Accounts.php
 * Copyright (c) 2019 - 2019 thegrumpydictator@gmail.com
 *
 * This file is part of the Firefly III CSV importer
 * (https://github.com/firefly-iii-csv-importer).
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


use App\Services\CSV\Converter\Amount;
use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Request\GetSearchAccountRequest;
use App\Services\FireflyIIIApi\Response\GetAccountsResponse;
use Log;

/**
 * Class Accounts
 */
class Accounts extends AbstractTask
{

    /**
     * @param array $group
     *
     * @return array
     */
    public function process(array $group): array
    {
        foreach ($group['transactions'] as $index => $transaction) {
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
     * TODO move to own class.
     *
     * @param array        $array
     *
     * @param Account|null $defaultAccount
     *
     * @return array
     * @throws \App\Exceptions\ApiHttpException
     */
    private function findAccount(array $array, ?Account $defaultAccount): array
    {
        Log::debug('Now in findAccount', $array);
        $result = null;
        // search ID
        if (is_int($array['id']) && $array['id'] > 0) {
            $result = $this->findByField('id', (string)$array['id']);
        }

        // search name
        if (null === $result && is_string($array['name']) && '' !== $array['name']) {
            $result = $this->findByField('name', $array['name']);
        }

        // search IBAN
        if (null === $result && is_string($array['iban']) && '' !== $array['iban']) {
            $result = $this->findByField('iban', $array['iban']);
        }

        // search number
        if (null === $result && is_string($array['number']) && '' !== $array['number']) {
            $result = $this->findByField('number', $array['number']);
        }
        if (null !== $result) {
            return $result->toArray();
        }

        Log::debug('Found no account or haven\'t searched for one.');

        // append an empty type to the array for consistency's sake.
        $array['type'] = $array['type'] ?? null;
        $array['bic']  = $array['bic'] ?? null;

        // if the default account is not NULL, return that one instead:
        if (null !== $defaultAccount) {
            return $defaultAccount->toArray();
        }

        return $array;
    }

    /**
     * @param string $field
     * @param string $value
     *
     * @return Account|null
     * @throws \App\Exceptions\ApiHttpException
     */
    private function findByField(string $field, string $value): ?Account
    {
        Log::debug(sprintf('Going to search account with "%s" "%s"', $field, $value));
        $request = new GetSearchAccountRequest();
        $request->setField($field);
        $request->setQuery($value);
        /** @var GetAccountsResponse $response */
        $response = $request->get();
        if (1 === count($response)) {
            /** @var Account $account */
            $account = $response->current();
            Log::debug(sprintf('Found %s account #%d based on "%s" "%s"', $account->type, $account->id, $field, $value));

            return $account;
        }

        return null;
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
     * @throws \App\Exceptions\ApiHttpException
     */
    private function processTransaction(array $transaction): array
    {
        $sourceArray = $this->getSourceArray($transaction);
        $destArray   = $this->getDestinationArray($transaction);
        $source      = $this->findAccount($sourceArray, $this->account);
        $destination = $this->findAccount($destArray, null);

        if (-1 === bccomp('0', $transaction['amount'])) {
            // amount is positive
            $transaction         = $this->setSource($transaction, $source);
            $transaction         = $this->setDestination($transaction, $destination);
            $transaction['type'] = $this->determineType($source['type'], $destination['type']);
        }

        if (1 === bccomp('0', $transaction['amount'])) {
            $transaction           = $this->setSource($transaction, $destination);
            $transaction           = $this->setDestination($transaction, $source);
            $transaction['amount'] = Amount::positive($transaction['amount']);
            $transaction['type']   = $this->determineType($destination['type'], $source['type']);

            // also fix foreign amount
            if (isset($transaction['foreign_amount']) && null !== $transaction['foreign_amount']) {
                $transaction['foreign_amount'] = Amount::positive($transaction['foreign_amount']);
            }
        }

        // if new source ID is filled in, drop the other fields:
        if (0 !== $transaction['source_id'] && null !== $transaction['source_id']) {
            $transaction['source_name']   = null;
            $transaction['source_iban']   = null;
            $transaction['source_number'] = null;
        }
        // if new source ID is filled in, drop the other fields:
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
<?php
/**
 * ImportRoutineManager.php
 * Copyright (c) 2019 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III CSV Importer.
 *
 * Firefly III CSV Importer is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III CSV Importer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III CSV Importer.If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace App\Services\Import;

use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\Converter\Amount;
use App\Services\CSV\Specifics\SpecificService;
use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Model\Transaction;
use App\Services\FireflyIIIApi\Model\TransactionGroup;
use App\Services\FireflyIIIApi\Request\GetAccountRequest;
use App\Services\FireflyIIIApi\Request\GetCurrencyRequest;
use App\Services\FireflyIIIApi\Request\GetPreferenceRequest;
use App\Services\FireflyIIIApi\Request\GetSearchAccountRequest;
use App\Services\FireflyIIIApi\Request\PostTransactionRequest;
use App\Services\FireflyIIIApi\Response\GetAccountsResponse;
use App\Services\FireflyIIIApi\Response\GetCurrencyResponse;
use App\Services\FireflyIIIApi\Response\PostTransactionResponse;
use App\Services\FireflyIIIApi\Response\PreferenceResponse;
use App\Services\FireflyIIIApi\Response\ValidationErrorResponse;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\ResultSet;
use League\Csv\Statement;
use Log;
use RuntimeException;

/**
 * Class ImportRoutineManager
 */
class ImportRoutineManager
{
    /** @var Configuration */
    private $configuration;
    /** @var array */
    private $errors;
    /** @var LineConverter */
    private $lineConverter;
    /** @var LineProcessor */
    private $lineProcessor;
    /** @var array */
    private $messages;
    /** @var Reader */
    private $reader;
    /** @var array */
    private $warnings;

    /** @var int */
    private $total;

    /**
     * Collect info on the current job, hold it in memory.
     *
     * ImportRoutineManager constructor.
     */
    public function __construct()
    {
        Log::debug('Constructed ImportRoutineManager');

        // get line converter
        $this->lineConverter = new LineConverter();
        $this->total         = 0;
        $this->errors        = [];
        $this->messages      = [];
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;

        // get line processor
        $this->lineProcessor = new LineProcessor($this->configuration->getRoles(), $this->configuration->getMapping(), $this->configuration->getDoMapping());

    }

    /**
     * @param Reader $reader
     */
    public function setReader(Reader $reader): void
    {
        $this->reader = $reader;
    }

    /**
     * Start the import.
     */
    public function start(): void
    {
        Log::debug('Now in start()');
        $lines = $this->startImportLoop();
        $count = count($lines);
        Log::debug(sprintf('Total number of lines to process: %d', $count));

        // get standard currency in case we don't have it locally.
        // TODO move out and log
        $prefRequest = new GetPreferenceRequest;
        $prefRequest->setName('currencyPreference');
        /** @var PreferenceResponse $response */
        $response = $prefRequest->get();
        $code     = $response->getPreference()->data;
        $currencyRequest = new GetCurrencyRequest();
        $currencyRequest->setCode($code);
        /** @var GetCurrencyResponse $result */
        $result       = $currencyRequest->get();
        $currency     = $result->getCurrency();

        // get default import account (if set) in case CSV doesn't have it
        // TODO move out and log
        $defaultAccountId = $this->configuration->getDefaultAccount();
        $defaultAccount =null;
        if(null !== $defaultAccountId) {
            $accountRequest = new GetAccountRequest;
            $accountRequest->setId($defaultAccountId);
            $defaultAccount = $accountRequest->get();
        }

        $transactions = $this->convertToTransactions($lines);
        $this->total  = count($transactions);
        // TODO move to other objects.
        Log::debug(sprintf('Now looping and cleaning up %d groups.', $this->total));
        foreach ($transactions as $index => $group) {
            $groupCount = count($group['transactions']);
            Log::debug(sprintf('Now at group %d/%d (has %d transaction(s))', $index+1, $this->total, $groupCount));
            foreach ($group['transactions'] as $groupIndex => $transaction) {
                Log::debug(sprintf('Now at transaction %d/%d', $groupIndex+1,$groupCount));
                // set currency ID if not set in array
                if (
                    (0 === $transaction['currency_id'] || null === $transaction['currency_id']) &&
                    (null === $transaction['currency_code'] || '' === $transaction['currency_code'])) {
                    $transaction['currency_id']   = $currency->id;
                    $transaction['currency_code'] = null;
                    Log::debug(sprintf('Set currency to %d because it was NULL or empty.', $currency->id));
                }

                // modify amount:
                $transaction['amount'] = bcmul($transaction['amount'], $transaction['amount_modifier']);

                // do something with the collected tags.
                $transaction['tags'] = array_unique(array_merge(array_values($transaction['tags_space']), array_values($transaction['tags_comma'])));
                unset($transaction['tags_comma'], $transaction['tags_space']);

                // TODO api accepts that source + dest are equal

                // get source + dest accounts:
                $sourceArray        = [
                    'transaction_type' => $transaction['type'],
                    'id'               => $transaction['source_id'],
                    'name'             => $transaction['source_name'],
                    'iban'             => $transaction['source_iban'] ?? null,
                    'number'           => $transaction['source_number'] ?? null,
                    'bic'              => $transaction['source_bic'] ?? null,
                ];
                $destinationAccount = [
                    'transaction_type' => $transaction['type'],
                    'id'               => $transaction['destination_id'],
                    'name'             => $transaction['destination_name'],
                    'iban'             => $transaction['destination_iban'] ?? null,
                    'number'           => $transaction['destination_number'] ?? null,
                    'bic'              => $transaction['destination_bic'] ?? null,
                ];
                // TODO add warning when falling back on the default account.
                $source      = $this->findAccount($sourceArray, $defaultAccount->getAccount());
                $destination = $this->findAccount($destinationAccount,null);

                if (-1 === bccomp('0', $transaction['amount'])) {
                    // amount is positive
                    $transaction['source_id']     = $source['id'];
                    $transaction['source_name']   = $source['name'];
                    $transaction['source_iban']   = $source['iban'];
                    $transaction['source_number'] = $source['number'];
                    $transaction['source_bic']    = $source['bic'];

                    $transaction['destination_id']     = $destination['id'];
                    $transaction['destination_name']   = $destination['name'];
                    $transaction['destination_iban']   = $destination['iban'];
                    $transaction['destination_number'] = $destination['number'];
                    $transaction['destination_bic']    = $destination['bic'];

                    $transaction['type'] = $this->determineType($source['type'], $destination['type']);
                }

                if (1 === bccomp('0', $transaction['amount'])) {
                    // fix source
                    $transaction['source_id']     = $destination['id'];
                    $transaction['source_name']   = $destination['name'];
                    $transaction['source_iban']   = $destination['iban'];
                    $transaction['source_number'] = $destination['number'];
                    $transaction['source_bic']    = $destination['bic'];

                    $transaction['destination_id']     = $source['id'];
                    $transaction['destination_name']   = $source['name'];
                    $transaction['destination_iban']   = $source['iban'];
                    $transaction['destination_number'] = $source['number'];
                    $transaction['destination_bic']    = $source['bic'];

                    $transaction['amount'] = Amount::positive($transaction['amount']);
                    $transaction['type'] = $this->determineType($destination['type'], $source['type']);
                }

                // if source is NULL

                // if the source + destination have a type, we can say something about the
                // transaction type:


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
                $transactions[$index]['transactions'][$groupIndex] = $transaction;

            }
        }

        // for each transaction, push to API
        foreach ($transactions as $index => $transaction) {
            if (null === $transaction['group_title']) {
                unset($transaction['group_title']);
            }

            echo 'Submitting:' . "\n";
            echo json_encode($transaction, JSON_PRETTY_PRINT);
            echo "\n\n";

            $request = new PostTransactionRequest();
            $request->setBody($transaction);
            $response = $request->post();
            if ($response instanceof ValidationErrorResponse) {
                $responseErrors = [];
                foreach ($response->errors->messages() as $key => $errors) {
                    foreach ($errors as $error) {
                        $responseErrors[] = sprintf('%s: %s (original value: "%s")', $key, $error, $this->getOriginalValue($key, $transaction));
                    }
                }
                if (count($responseErrors) > 0) {
                    $this->addErrorArray($index, $responseErrors);
                }
            }

            if ($response instanceof PostTransactionResponse) {
                /** @var TransactionGroup $group */
                $group = $response->getTransactionGroup();
                /** @var Transaction $transaction */
                $transaction = $group->transactions[0];
                $message     = sprintf(
                    'Created %s #%d "%s" (%s %s)', $transaction->type, $group->id, $transaction->description, $transaction->currencyCode, $transaction->amount
                );
                $this->addMessageString($index, $message);
            }
        }
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param int   $index
     * @param array $errors
     */
    private function addErrorArray(int $index, array $errors): void
    {
        $this->errors[$index] = $errors;
    }

    /**
     * @param int    $index
     * @param string $message
     */
    private function addMessageString(int $index, string $message)
    {
        $this->messages[$index] = $message;
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    private function convertToTransactions(array $lines): array
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        return $this->lineConverter->convert($lines);
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }


    /**
     * @param string|null $sourceType
     * @param string|null $destinationType
     *
     * @return string
     */
    private function determineType(?string $sourceType, ?string $destinationType): string
    {
        if (null === $sourceType && null === $destinationType) {
            return 'withdrawal';
        }

        // if source is a asset and dest is NULL, its a withdrawal
        if('asset' === $sourceType && null === $destinationType){
            return 'withdrawal';
        }
        // if destination is asset and source is NULL, its a deposit
        if(null === $sourceType && 'asset' === $destinationType){
            return 'deposit';
        }

        $type = config(sprintf('transaction_types.account_to_transaction.%s.%s', $sourceType, $destinationType));

        return $type ?? 'withdrawal';
    }

    /**
     * TODO move to own class.
     *
     * @param array $array
     *
     * @return array
     * @throws \App\Exceptions\ApiHttpException
     */
    private function findAccount(array $array, ?Account $defaultAccount): array
    {
        Log::debug('Now in findAccount', $array);
        // search ID
        if (is_int($array['id']) && $array['id'] > 0) {
            Log::debug(sprintf('Going to search account with ID #%d', $array['id']));
            $request = new GetSearchAccountRequest();
            $request->setField('id');
            $request->setQuery($array['id']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                /** @var Account $account */
                $account = $response->current();
                Log::debug(sprintf('Found account #%d based on ID #%d', $account->id, $array['id']));
                return $account->toArray();
            }

            return $array;
        }

        // search name
        if (is_string($array['name']) && '' !== $array['name']) {
            Log::debug(sprintf('Going to search account with name "%s"', $array['name']));
            $request = new GetSearchAccountRequest();
            $request->setField('name');
            $request->setQuery($array['name']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                /** @var Account $account */
                $account = $response->current();
                Log::debug(sprintf('Found account #%d based on name "%s"', $account->id, $array['name']));
                return $account->toArray();
            }
            Log::debug('Found nothing on name.');
        }
        // search IBAN
        if (is_string($array['iban']) && '' !== $array['iban']) {
            Log::debug(sprintf('Going to search account with iban "%s"', $array['iban']));
            $request = new GetSearchAccountRequest();
            $request->setField('iban');
            $request->setQuery($array['iban']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                /** @var Account $account */
                $account = $response->current();
                Log::debug(sprintf('Found account #%d based on IBAN "%s"', $account->id, $array['iban']));
                return $account->toArray();
            }
            Log::debug('Found nothing on IBAN.');
        }

        // search number
        if (is_string($array['number']) && '' !== $array['number']) {
            Log::debug(sprintf('Going to search account with number "%s"', $array['number']));
            $request = new GetSearchAccountRequest();
            $request->setField('number');
            $request->setQuery($array['number']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                /** @var Account $account */
                $account = $response->current();
                Log::debug(sprintf('Found account #%d based on account number "%s"', $account->id, $array['number']));
                return $account->toArray();
            }
            Log::debug('Found nothing on number.');
        }
        Log::debug('Found no account or haven\'t searched for one.');

        // append an empty type to the array for consistency's sake.
        $array['type'] = $array['type'] ?? null;
        $array['bic']  = $array['bic'] ?? null;
        // if the default account is not NULL, return that one instead:
        if(null !== $defaultAccount) {
            return $defaultAccount->toArray();
        }

        return $array;
    }

    /**
     * @param string $key
     * @param array  $transaction
     *
     * @return string
     */
    private function getOriginalValue(string $key, array $transaction): string
    {
        $parts = explode('.', $key);
        if (3 !== count($parts)) {
            return '(unknown)';
        }
        $index = (int)$parts[1];

        return $transaction['transactions'][$index][$parts[2]] ?? '(not found)';
    }

    /**
     * Loop all records from CSV file.
     *
     * @param ResultSet $records
     *
     * @return array
     */
    private function loopRecords(ResultSet $records): array
    {
        $updatedRecords = [];
        $count          = $records->count();
        Log::debug(sprintf('Now in loopRecords() with %d records', $count));
        $currentIndex = 1;
        foreach ($records as $index => $line) {
            $line = $this->sanitize($line);
            Log::debug(sprintf('In loop %d/%d', $currentIndex, $count));
            $line             = SpecificService::runSpecifics($line, $this->configuration->getSpecifics());
            $updatedRecords[] = $this->processRecord($line);
            $currentIndex++;
        }

        return $updatedRecords;
    }

    /**
     * @param array $line
     *
     * @return array
     */
    private function processRecord(array $line): array
    {
        Log::debug('Now in processRecord()');
        $updatedLine = $this->lineProcessor->process($line);

        // TODO any other steps go here.

        return $updatedLine;
    }

    /**
     * Do a first sanity check on whatever comes out of the CSV file.
     *
     * @param array $line
     *
     * @return array
     */
    private function sanitize(array $line): array
    {
        $lineValues = array_values($line);
        array_walk(
            $lineValues, static function ($element) {
            $element = trim(str_replace('&nbsp;', ' ', (string)$element));

            return $element;
        }
        );

        return $lineValues;
    }

    /**
     * Get a reader, and start looping over each line.
     *
     * @return array
     */
    private function startImportLoop(): array
    {
        Log::debug('Now in startImportLoop()');
        $offset = $this->configuration->isHeaders() ? 1 : 0;

        Log::debug(sprintf('Offset is %d', $offset));
        try {
            $stmt    = (new Statement)->offset($offset);
            $records = $stmt->process($this->reader);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new RuntimeException($e->getMessage());
        }

        return $this->loopRecords($records);
    }

}

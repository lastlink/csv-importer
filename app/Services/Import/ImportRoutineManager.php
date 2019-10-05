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
use App\Services\CSV\File\FileReader;
use App\Services\CSV\Specifics\SpecificService;
use App\Services\FireflyIIIApi\Request\GetCurrencyRequest;
use App\Services\FireflyIIIApi\Request\GetPreferenceRequest;
use App\Services\FireflyIIIApi\Request\GetSearchAccountRequest;
use App\Services\FireflyIIIApi\Request\PostTransactionRequest;
use App\Services\FireflyIIIApi\Response\GetAccountsResponse;
use App\Services\FireflyIIIApi\Response\GetCurrencyResponse;
use App\Services\FireflyIIIApi\Response\PreferenceResponse;
use App\Services\FireflyIIIApi\Response\ValidationErrorResponse;
use App\Services\Session\Constants;
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
    private $errorMessages;
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

    /**
     * Collect info on the current job, hold it in memory.
     *
     * ImportRoutineManager constructor.
     */
    public function __construct()
    {
        Log::debug('Constructed ImportRoutineManager');
        // get config:
        $this->configuration = Configuration::fromArray(session()->get(Constants::CONFIGURATION));

        // get line processor
        $this->lineProcessor = new LineProcessor($this->configuration->getRoles(), $this->configuration->getMapping(), $this->configuration->getDoMapping());

        // get line converter
        $this->lineConverter = new LineConverter();

        // get reader
        $this->reader = FileReader::getReaderFromSession();
    }

    /**
     * Start the import.
     */
    public function start(): void
    {
        Log::debug('Now in start()');
        $lines = $this->startImportLoop();
        $count = count($lines);
        Log::debug(sprintf('Total number of lines: %d', $count));

        // get standard currency in case we don't have it locally.
        $prefRequest = new GetPreferenceRequest;
        $prefRequest->setName('currencyPreference');
        /** @var PreferenceResponse $response */
        $response = $prefRequest->get();
        $code     = $response->getPreference()->data;

        $currencyRequest = new GetCurrencyRequest();
        $currencyRequest->setCode($code);
        /** @var GetCurrencyResponse $result */
        $result   = $currencyRequest->get();
        $currency = $result->getCurrency();
        // currencyPreference


        $transactions = $this->convertToTransactions($lines);
        // improve accounts

        // TODO move to other objects.
        // TODO API must be able to handle NULL: group_title, currency_code, foreign_curency_id, foreign_currency_code
        foreach ($transactions as $index => $group) {
            foreach ($group['transactions'] as $groupIndex => $transaction) {


                if (0 === $transactions[$index]['transactions'][$groupIndex]['currency_id']
                    && null === $transactions[$index]['transactions'][$groupIndex]['currency_code']) {
                    $transactions[$index]['transactions'][$groupIndex]['currency_id'] = $currency->id;
                    unset($transactions[$index]['transactions'][$groupIndex]['currency_code']);
                }
                if (null === $transactions[$index]['transactions'][$groupIndex]['foreign_currency_code']) {
                    unset($transactions[$index]['transactions'][$groupIndex]['foreign_currency_code']);
                }
                if(0=== $transactions[$index]['transactions'][$groupIndex]['destination_id']) {
                    unset($transactions[$index]['transactions'][$groupIndex]['destination_id']);
                }
                $sourceArray = [
                    'transaction_type' => $transaction['type'],
                    'id'               => $transaction['source_id'],
                    'name'             => $transaction['source_name'],
                    'iban'             => $transaction['source_iban'],
                    'number'           => $transaction['source_number'],
                ];
                $source      = $this->findAccount($sourceArray);
                if (0 !== $source['id']) {
                    $transactions[$index]['transactions'][$groupIndex]['source_id']     = $source['id'];
                    $transactions[$index]['transactions'][$groupIndex]['source_name']   = null;
                    $transactions[$index]['transactions'][$groupIndex]['source_iban']   = null;
                    $transactions[$index]['transactions'][$groupIndex]['source_number'] = null;
                }

                $destinationAccount = [
                    'transaction_type' => $transaction['type'],
                    'id'               => $transaction['destination_id'],
                    'name'             => $transaction['destination_name'],
                    'iban'             => $transaction['destination_iban'] ?? null,
                    'number'           => $transaction['destination_number'],
                ];

                $destination = $this->findAccount($destinationAccount);
                if (0 !== $destination['id']) {
                    $transactions[$index]['transactions'][$groupIndex]['destination_id']     = $destination['id'];
                    $transactions[$index]['transactions'][$groupIndex]['destination_name']   = null;
                    $transactions[$index]['transactions'][$groupIndex]['destination_iban']   = null;
                    $transactions[$index]['transactions'][$groupIndex]['destination_number'] = null;
                }
            }
        }


        // for each transaction, push to API
        foreach ($transactions as $transaction) {

            if (null === $transaction['group_title']) {
                unset($transaction['group_title']);
            }

            $request = new PostTransactionRequest();
            $request->setBody($transaction);
            $response = $request->post();
            if ($response instanceof ValidationErrorResponse) {
                var_dump($transaction);
                foreach ($response->errors->messages() as $key => $errors) {
                    var_dump($key);
                    foreach ($errors as $error) {
                        var_dump($error);
                    }
                }
                exit;
            }
        }
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    private function convertToTransactions(array $lines): array
    {
        return $this->lineConverter->convert($lines);
    }

    /**
     * TODO move to own class.
     *
     * @param array $array
     *
     * @return array
     */
    private function findAccount(array $array): array
    {

        // search ID
        if (is_int($array['id']) && $array['id'] > 0) {
            $request = new GetSearchAccountRequest();
            $request->setField('id');
            $request->setQuery($array['id']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                return $response->current()->toArray();
            }

            return $array;
        }

        // search name
        if (is_string($array['name']) && '' !== $array['name']) {
            $request = new GetSearchAccountRequest();
            $request->setField('name');
            $request->setQuery($array['name']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                return $response->current()->toArray();
            }
        }
        // search IBAN
        if (is_string($array['iban']) && '' !== $array['iban']) {
            $request = new GetSearchAccountRequest();
            $request->setField('iban');
            $request->setQuery($array['iban']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                return $response->current()->toArray();
            }
        }

        // search number
        if (is_string($array['number']) && '' !== $array['number']) {
            $request = new GetSearchAccountRequest();
            $request->setField('number');
            $request->setQuery($array['number']);
            /** @var GetAccountsResponse $response */
            $response = $request->get();
            if (1 === count($response)) {
                return $response->current()->toArray();
            }
        }

        return $array;
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
        foreach ($records as $index => $line) {
            $line = $this->sanitize($line);
            Log::debug(sprintf('In loop %d/%d', $index + 1, $count));
            $line             = SpecificService::runSpecifics($line, $this->configuration->getSpecifics());
            $updatedRecords[] = $this->processRecord($line);
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
        unset($processor);

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

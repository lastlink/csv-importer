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
use App\Services\CSV\Specifics\SpecificService;
use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Model\Transaction;
use App\Services\FireflyIIIApi\Model\TransactionCurrency;
use App\Services\FireflyIIIApi\Model\TransactionGroup;
use App\Services\FireflyIIIApi\Request\PostTransactionRequest;
use App\Services\FireflyIIIApi\Response\PostTransactionResponse;
use App\Services\FireflyIIIApi\Response\ValidationErrorResponse;
use App\Services\Import\Routine\APISubmitter;
use App\Services\Import\Routine\ColumnValueConverter;
use App\Services\Import\Routine\LineProcessor;
use App\Services\Import\Routine\PseudoTransactionProcessor;
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
    /** @var TransactionCurrency Used as a fallback when creating transactions. */
    private $defaultCurrency;
    /** @var Account Used as a fallback when creating transactions */
    private $defaultAccount;
    /** @var ColumnValueConverter */
    private $columnValueConverter;
    /** @var PseudoTransactionProcessor */
    private $pseudoTransactionProcessor;
    /** @var APISubmitter */
    private $apiSubmitter;
    /**
     * Collect info on the current job, hold it in memory.
     *
     * ImportRoutineManager constructor.
     */
    public function __construct()
    {
        Log::debug('Constructed ImportRoutineManager');

        // get line converter
        $this->columnValueConverter = new ColumnValueConverter;
        $this->apiSubmitter         = new APISubmitter;
        $this->total                = 0;
        $this->errors               = [];
        $this->messages             = [];
    }

    /**
     * @param Configuration $configuration
     *
     * @throws \App\Exceptions\ApiHttpException
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;

        // get line processor
        $this->lineProcessor              = new LineProcessor($this->configuration->getRoles(), $this->configuration->getMapping(), $this->configuration->getDoMapping());
        $this->pseudoTransactionProcessor = new PseudoTransactionProcessor($this->configuration->getDefaultAccount());

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

        // convert CSV file into raw lines (arrays)
        $CSVLines = $this->processCSVFile();

        // convert raw lines into arrays with individual ColumnValues
        $valueArrays = $this->lineProcessor->processCSVLines($CSVLines);

        // convert value arrays into (pseudo) transactions.
        $pseudo = $this->columnValueConverter->processValueArrays($valueArrays);

        // convert pseudo transactions into actual transactions.
        $transactions = $this->pseudoTransactionProcessor->processPseudo($pseudo);

        // submit transactions to API:
        $report = $this->apiSubmitter->processTransactions($transactions);
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
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Loop all records from CSV file.
     *
     * @param ResultSet $records
     *
     * @return array
     */
    private function processCSVLines(ResultSet $records): array
    {
        $updatedRecords = [];
        $count          = $records->count();
        Log::debug(sprintf('Now in processCSVLines() with %d records', $count));
        $currentIndex = 1;
        foreach ($records as $index => $line) {
            $line = $this->sanitize($line);
            Log::debug(sprintf('In loop %d/%d', $currentIndex, $count));
            $line             = SpecificService::runSpecifics($line, $this->configuration->getSpecifics());
            $updatedRecords[] = $line;
            $currentIndex++;
        }

        return $updatedRecords;
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
    private function processCSVFile(): array
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

        return $this->processCSVLines($records);
    }

}

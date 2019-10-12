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
use App\Services\Import\Routine\APISubmitter;
use App\Services\Import\Routine\ColumnValueConverter;
use App\Services\Import\Routine\CSVFileProcessor;
use App\Services\Import\Routine\LineProcessor;
use App\Services\Import\Routine\PseudoTransactionProcessor;
use League\Csv\Reader;
use Log;

/**
 * Class ImportRoutineManager
 */
class ImportRoutineManager
{
    /** @var Configuration */
    private $configuration;
    /** @var LineProcessor */
    private $lineProcessor;
    /** @var Reader */
    private $reader;
    /** @var ColumnValueConverter */
    private $columnValueConverter;
    /** @var PseudoTransactionProcessor */
    private $pseudoTransactionProcessor;
    /** @var APISubmitter */
    private $apiSubmitter;
    /** @var CSVFileProcessor */
    private $csvFileProcessor;

    /** @var array */
    private $allMessages;
    /** @var array */
    private $allWarnings;
    /** @var array */
    private $allErrors;
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
        $this->csvFileProcessor     = new CSVFileProcessor;
        $this->allMessages          = [];
        $this->allWarnings          = [];
        $this->allErrors            = [];
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
        $this->csvFileProcessor->setReader($reader);
    }

    /**
     * @return array
     */
    public function getAllMessages(): array
    {
        return $this->allMessages;
    }

    /**
     * @return array
     */
    public function getAllWarnings(): array
    {
        return $this->allWarnings;
    }

    /**
     * @return array
     */
    public function getAllErrors(): array
    {
        return $this->allErrors;
    }

    /**
     * Start the import.
     */
    public function start(): void
    {
        Log::debug('Now in start()');

        // convert CSV file into raw lines (arrays)
        $this->csvFileProcessor->setSpecifics($this->configuration->getSpecifics());
        $this->csvFileProcessor->setHasHeaders($this->configuration->isHeaders());
        $CSVLines = $this->csvFileProcessor->processCSVFile();

        // convert raw lines into arrays with individual ColumnValues
        $valueArrays = $this->lineProcessor->processCSVLines($CSVLines);

        // convert value arrays into (pseudo) transactions.
        $pseudo = $this->columnValueConverter->processValueArrays($valueArrays);

        // convert pseudo transactions into actual transactions.
        $transactions = $this->pseudoTransactionProcessor->processPseudo($pseudo);

        // submit transactions to API:
        $this->apiSubmitter->processTransactions($transactions);

        $count = count($CSVLines);
        $this->mergeMessages($count);
        $this->mergeWarnings($count);
        $this->mergeErrors($count);
    }

    /**
     * @param int $count
     */
    private function mergeMessages(int $count): void
    {
        $one   = $this->csvFileProcessor->getMessages();
        $two   = $this->lineProcessor->getMessages();
        $three = $this->columnValueConverter->getMessages();
        $four  = $this->pseudoTransactionProcessor->getMessages();
        $five  = $this->apiSubmitter->getMessages();
        $total = [];
        for ($i = 0; $i < $count; $i++) {
            $total[$i] = array_merge(
                $one[$i] ?? [],
                $two[$i] ?? [],
                $three[$i] ?? [],
                $four[$i] ?? [],
                $five[$i] ?? []
            );
        }

        $this->allMessages = $total;
    }

    /**
     * @param int $count
     */
    private function mergeWarnings(int $count): void
    {
        $one   = $this->csvFileProcessor->getWarnings();
        $two   = $this->lineProcessor->getWarnings();
        $three = $this->columnValueConverter->getWarnings();
        $four  = $this->pseudoTransactionProcessor->getWarnings();
        $five  = $this->apiSubmitter->getWarnings();
        $total = [];
        for ($i = 0; $i < $count; $i++) {
            $total[$i] = array_merge(
                $one[$i] ?? [],
                $two[$i] ?? [],
                $three[$i] ?? [],
                $four[$i] ?? [],
                $five[$i] ?? []
            );
        }

        $this->allWarnings = $total;
    }


    /**
     * @param int $count
     */
    private function mergeErrors(int $count): void
    {
        $one   = $this->csvFileProcessor->getErrors();
        $two   = $this->lineProcessor->getErrors();
        $three = $this->columnValueConverter->getErrors();
        $four  = $this->pseudoTransactionProcessor->getErrors();
        $five  = $this->apiSubmitter->getErrors();
        $total = [];
        for ($i = 0; $i < $count; $i++) {
            $total[$i] = array_merge(
                $one[$i] ?? [],
                $two[$i] ?? [],
                $three[$i] ?? [],
                $four[$i] ?? [],
                $five[$i] ?? []
            );
        }

        $this->allErrors = $total;
    }

}

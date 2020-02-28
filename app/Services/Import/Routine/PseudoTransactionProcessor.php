<?php
/**
 * PseudoTransactionProcessor.php
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

namespace App\Services\Import\Routine;

use App\Exceptions\ApiHttpException;
use App\Exceptions\ImportException;
use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Model\TransactionCurrency;
use App\Services\FireflyIIIApi\Request\GetAccountRequest;
use App\Services\FireflyIIIApi\Request\GetCurrencyRequest;
use App\Services\FireflyIIIApi\Request\GetPreferenceRequest;
use App\Services\FireflyIIIApi\Response\GetAccountResponse;
use App\Services\FireflyIIIApi\Response\GetCurrencyResponse;
use App\Services\FireflyIIIApi\Response\PreferenceResponse;
use App\Services\Import\Support\ProgressInformation;
use App\Services\Import\Task\AbstractTask;
use Log;

/**
 * Class PseudoTransactionProcessor
 */
class PseudoTransactionProcessor
{
    use ProgressInformation;

    /** @var array */
    private $tasks;

    /** @var Account */
    private $defaultAccount;

    /** @var TransactionCurrency */
    private $defaultCurrency;

    /**
     * PseudoTransactionProcessor constructor.
     *
     * @param int|null $defaultAccountId
     *
     * @throws ImportException
     */
    public function __construct(?int $defaultAccountId)
    {
        $this->tasks = config('csv_importer.transaction_tasks');
        $this->getDefaultAccount($defaultAccountId);
        $this->getDefaultCurrency();
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    public function processPseudo(array $lines): array
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        $count     = count($lines);
        $processed = [];
        Log::info(sprintf('Converting %d lines into transactions.', $count));
        /** @var array $line */
        foreach ($lines as $index => $line) {
            $processed[] = $this->processPseudoLine($index, $line);
            //sleep(1); // DEBUG
        }
        Log::info(sprintf('Done converting %d lines into transactions.', $count));

        return $processed;

    }

    /**
     * @param int|null $accountId
     *
     * @throws ImportException
     */
    private function getDefaultAccount(?int $accountId): void
    {
        if (null !== $accountId) {
            $accountRequest = new GetAccountRequest;
            $accountRequest->setId($accountId);
            /** @var GetAccountResponse $result */
            try {
                $result = $accountRequest->get();
            } catch (ApiHttpException $e) {
                Log::error($e->getMessage());
                throw new ImportException(sprintf('The default account in your configuration file (%d) does not exist.', $accountId));
            }
            $this->defaultAccount = $result->getAccount();
        }
    }

    /**
     * @throws ImportException
     */
    private function getDefaultCurrency(): void
    {
        $prefRequest = new GetPreferenceRequest;
        $prefRequest->setName('currencyPreference');

        try {
            /** @var PreferenceResponse $response */
            $response = $prefRequest->get();
        } catch (ApiHttpException $e) {
            Log::error($e->getMessage());
            throw new ImportException('Could not load the users currency preference.');
        }
        $code            = $response->getPreference()->data ?? 'EUR';
        $currencyRequest = new GetCurrencyRequest();
        $currencyRequest->setCode($code);
        try {
            /** @var GetCurrencyResponse $result */
            $result                = $currencyRequest->get();
            $this->defaultCurrency = $result->getCurrency();
        } catch (ApiHttpException $e) {
            Log::error($e->getMessage());
            throw new ImportException(sprintf('The default currency ("%s") could not be loaded.', $code));
        }
    }

    /**
     * @param int   $index
     * @param array $line
     *
     * @return array
     */
    private function processPseudoLine(int $index, array $line): array
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        foreach ($this->tasks as $task) {
            /** @var AbstractTask $object */
            $object = app($task);
            Log::debug(sprintf('Now running task %s', $task));

            if ($object->requiresDefaultAccount()) {
                $object->setAccount($this->defaultAccount);
            }
            if ($object->requiresTransactionCurrency()) {
                $object->setTransactionCurrency($this->defaultCurrency);
            }

            $line = $object->process($line);
        }

        return $line;
    }

}
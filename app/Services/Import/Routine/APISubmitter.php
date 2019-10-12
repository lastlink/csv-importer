<?php
/**
 * APISubmitter.php
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

namespace App\Services\Import\Routine;

use App\Services\FireflyIIIApi\Model\Transaction;
use App\Services\FireflyIIIApi\Model\TransactionGroup;
use App\Services\FireflyIIIApi\Request\PostTransactionRequest;
use App\Services\FireflyIIIApi\Response\PostTransactionResponse;
use App\Services\FireflyIIIApi\Response\ValidationErrorResponse;

/**
 * Class APISubmitter
 */
class APISubmitter
{
    /**
     * @param array $lines
     *
     * @return array
     */
    public function processTransactions(array $lines): array
    {
        $report = [];
        /**
         * @var int   $index
         * @var array $line
         */
        foreach ($lines as $index => $line) {
            $this->processTransaction($index, $line);
        }

        return $report;
    }

    /**
     * @param int   $index
     * @param array $line
     */
    private function processTransaction(int $index, array $line): void
    {
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
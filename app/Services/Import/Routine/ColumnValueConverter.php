<?php
/**
 * ColumnValueConverter.php
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

use App\Services\Import\ColumnValue;
use App\Services\Import\Support\ProgressInformation;
use Log;
use RuntimeException;

/**
 * Class ColumnValueConverter
 *
 * Converts rows of ColumnValue's to pseudo transactions.
 * Pseudo because they still require some lookups and cleaning up.
 */
class ColumnValueConverter
{
    use ProgressInformation;

    /** @var array */
    private $roleToTransaction;

    public function __construct()
    {
        $this->roleToTransaction = config('csv_importer.role_to_transaction');
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    public function processValueArrays(array $lines): array
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        $processed = [];

        foreach ($lines as $index => $line) {
            $processed[] = $this->processValueArray($index, $line);
        }

        return $processed;
    }

    /**
     * @param int   $index
     * @param array $line
     *
     * @return array
     */
    private function processValueArray(int $index, array $line): array
    {
        $count = count($line);
        Log::debug(sprintf('Now in %s with %d columns in this line.', __METHOD__, $count));
        // make a new transaction:
        $transaction = [
            //'user'          => 1, // ??
            'group_title'  => null,
            'transactions' => [
                [
                    //'user'=> 1,
                    'type'             => 'withdrawal',
                    'date'             => '',
                    'currency_id'      => null,
                    'currency_code'    => null,
                    //'foreign_currency_id'   => null,
                    //'foreign_currency_code' => null,
                    'amount'           => null,
                    'amount_modifier'  => '1', // 1 or -1
                    //'foreign_amount'        => null,
                    'description'      => null,
                    'source_id'        => null,
                    'source_name'      => null,
                    //'source_iban'           => null, // TODO unsupported by API!
                    //'source_number'         => null, // TODO unsupported by API!
                    //'source_bic'            => null, // TODO unsupported by API.
                    'destination_id'   => null,
                    'destination_name' => null,
                    //'destination_iban'      => null, // TODO unsupported by API!
                    //'destination_number'    => null, // TODO unsupported by API!
                    //'destination_bic'       => null, // TODO unsupported by API.
                    //'budget_id'             => null,
                    //'budget_name'           => null,
                    //'category_id'           => null,
                    //'category_name'         => null,
                    //'bill_id'               => null,
                    //'bill_name'             => null,
                    //'piggy_bank_id'         => null,
                    //'piggy_bank_name'       => null,
                    //'reconciled'            => false,
                    //'notes'                 => null,
                    //'tags'                  => [],
                    //'internal_reference'    => null,
                    //'external_id'           => null,
                    //'original_source'       => null,
                    //'recurrence_id'         => null,
                    //'bunq_payment_id'       => null,
                    //'importHashV2'          => null,
                    //'sepa_cc'               => null,
                    //'sepa_ct_op'            => null,
                    //'sepa_ct_id'            => null,
                    //'sepa_db'               => null,
                    //'sepa_country'          => null,
                    //'sepa_ep'               => null,
                    //'sepa_ci'               => null,
                    //'sepa_batch_id'         => null,
                    //'interest_date'         => null,
                    //'book_date'             => null,
                    //'process_date'          => null,
                    //'due_date'              => null,
                    //'payment_date'          => null,
                    //'invoice_date'          => null,
                    'tags_comma'       => [],
                    'tags_space'       => [],
                    // extra fields for amounts:
                    'amount_debit'     => null,
                    'amount_credit'    => null,
                    'amount_negated'   => null,
                ],
            ],
        ];
        /**
         * @var int         $columnIndex
         * @var ColumnValue $value
         */
        foreach ($line as $columnIndex => $value) {
            $role             = $value->getRole();
            $transactionField = $this->roleToTransaction[$role] ?? null;
            $parsedValue      = $value->getParsedValue();
            if (null === $transactionField) {
                throw new RuntimeException(sprintf('No place for role "%s"', $value->getRole()));
            }
            if (null === $parsedValue) {
                Log::debug(sprintf('Skip column #%d with role "%s" (in field "%s")', $columnIndex, $role, $transactionField));
                continue;
            }
            Log::debug(
                sprintf('Stored column #%d with value"%s" and role "%s" in field "%s"', $columnIndex, $this->toString($parsedValue), $role, $transactionField)
            );
            $transaction['transactions'][0][$transactionField] = $parsedValue;
        }
        Log::debug('Final transaction', $transaction);

                $this->addWarning($index,'Warning from Column value converter.');
        //        $this->addMessage($index,'Message from Column value converter.');
        //        $this->addError($index,'Error from Column value converter.');

        return $transaction;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function toString($value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        return (string)$value;
    }

}
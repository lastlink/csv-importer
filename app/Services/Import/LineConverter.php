<?php
/**
 * LineConverter.php
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

use RuntimeException;

/**
 * Class LineConverter
 */
class LineConverter
{
    /** @var array */
    private $roleToTransaction;

    /**
     * LineConverter constructor.
     */
    public function __construct()
    {
        $this->roleToTransaction = config('csv_importer.role_to_transaction');
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    public function convert(array $lines): array
    {
        $return = [];
        foreach ($lines as $index => $line) {
            $return[] = $this->convertLine($line);
        }

        return $return;
    }

    /**
     * @param array $line
     *
     * @return array
     */
    private function convertLine(array $line): array
    {
        // make a new transaction:
        $transaction = [
            //'user'          => 1, // ??
            'group_title'  => null,
            'transactions' => [
                [
                    //'user'=> 1,
                    'type'                  => 'withdrawal',
                    'date'                  => '',
                    'currency_id'           => null,
                    //'currency_code'         => null,
                    //'foreign_currency_id'   => null,
                    //'foreign_currency_code' => null,
                    'amount'                => null,
                    //'foreign_amount'        => null,
                    'description'           => null,
                    'source_id'             => null,
                    'source_name'           => null,
                    //'source_iban'           => null, // TODO unsupported by API!
                    //'source_number'         => null, // TODO unsupported by API!
                    //'source_bic'            => null, // TODO unsupported by API.
                    'destination_id'        => null,
                    'destination_name'      => null,
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
            if (null === $transactionField) {
                throw new RuntimeException(sprintf('No place for role "%s"', $value->getRole()));
            }
            $transaction['transactions'][0][$transactionField] = $value->getParsedValue();
        }

        return $transaction;
    }
}

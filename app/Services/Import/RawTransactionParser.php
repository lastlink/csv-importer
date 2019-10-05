<?php
/**
 * RawTransactionParser.php
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

/**
 * Class RawTransactionParser
 */
class RawTransactionParser
{

    /** @var array */
    private $sourceAccounts;

    public function __construct()
    {
        $this->sourceAccounts = [];
    }

    /**
     * @param array $transaction
     *
     * @return array
     */
    public function parse(array $transaction): array
    {
        $sourceAccount = [
            'source_id'     => $transaction['source_id'],
            'source_name'   => $transaction['source_name'],
            'source_iban'   => $transaction['source_iban'],
            'source_number' => $transaction['source_number'],
            'source_bic'    => $transaction['source_bic'],
        ];
        $account       = 0;
        var_dump($transaction);
        exit;
    }

    /**
     * @param array $transactions
     *
     * @return array
     */
    public function parseAll(array $transactions): array
    {
        $result = [];
        /** @var array $transaction */
        foreach ($transactions as $transaction) {
            $result[] = $this->parse($transaction);
        }

        return $result;
    }


}

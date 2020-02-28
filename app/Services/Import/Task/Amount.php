<?php
/**
 * Amount.php
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

namespace App\Services\Import\Task;

use App\Services\CSV\Converter\Amount as AmountConverter;
use Log;

/**
 * Class Amount
 */
class Amount extends AbstractTask
{
    /**
     * @param array $group
     *
     * @return array
     */
    public function process(array $group): array
    {
        foreach ($group['transactions'] as $index => $transaction) {
            $group['transactions'][$index] = $this->processAmount($transaction);
        }

        return $group;
    }

    /**
     * @param array $transaction
     *
     * @return array
     */
    private function processAmount(array $transaction): array
    {
        Log::debug(sprintf('Now at the start of processAmount(%s)', $transaction['amount']));
        // modify amount:
        $transaction['amount'] = bcmul($transaction['amount'], $transaction['amount_modifier']);

        Log::debug(sprintf('Amount is now %s.', $transaction['amount']));

        // modify foreign amount
        if (isset($transaction['foreign_amount']) && null !== $transaction['foreign_amount']) {
            $transaction['foreign_amount'] = bcmul($transaction['foreign_amount'], $transaction['amount_modifier']);
            Log::debug(sprintf('FOREIGN amount is now %s.', $transaction['foreign_amount']));
        }

        // amount is overruled by amount_debit:
        if (null !== $transaction['amount_debit'] && 0 !== bccomp('0', $transaction['amount_debit'])) {
            Log::debug(sprintf('Debit amount is "%s" which trumps the normal amount.', $transaction['amount_debit']));
            $transaction['amount'] = AmountConverter::negative($transaction['amount_debit']);
        }
        Log::debug(sprintf('Amount (after amount_debit) is now %s.', $transaction['amount']));

        // then credit
        if (null !== $transaction['amount_credit'] && 0 !== bccomp('0', $transaction['amount_credit'])) {
            Log::debug(sprintf('Credit amount is "%s" which trumps the normal amount.', $transaction['amount_credit']));
            $transaction['amount'] = AmountConverter::positive($transaction['amount_credit']);
        }
        Log::debug(sprintf('Amount (after amount_credit) is now %s.', $transaction['amount']));

        // then negated
        if (null !== $transaction['amount_negated'] && 0 !== bccomp('0', $transaction['amount_negated'])) {
            Log::debug(sprintf('Negated amount is "%s" which trumps the normal amount.', $transaction['amount_negated']));
            $transaction['amount'] = AmountConverter::negative($transaction['amount_negated']);
        }
        Log::debug(sprintf('Amount (after amount_negated) is now %s.', $transaction['amount']));
        // unset those fields:
        unset($transaction['amount_credit'], $transaction['amount_debit'], $transaction['amount_negated']);

        return $transaction;
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
     * Returns true if the task requires the default account.
     *
     * @return bool
     */
    public function requiresDefaultAccount(): bool
    {
        return false;
    }
}
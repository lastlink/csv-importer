<?php
declare(strict_types=1);
/**
 * TransactionCurrencies.php
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

namespace App\Services\CSV\Mapper;

use GrumpyDictator\FFIIIApiSupport\Exceptions\ApiHttpException;
use GrumpyDictator\FFIIIApiSupport\Model\TransactionCurrency;
use GrumpyDictator\FFIIIApiSupport\Request\GetCurrenciesRequest;

/**
 * Class TransactionCurrencies
 */
class TransactionCurrencies implements MapperInterface
{

    /**
     * Get map of objects.
     *
     * @return array
     * @throws ApiHttpException
     */
    public function getMap(): array
    {
        $result   = [];
        $uri     = (string)config('csv_importer.uri');
        $token   = (string)config('csv_importer.access_token');
        $request  = new GetCurrenciesRequest($uri, $token);
        $response = $request->get();
        /** @var TransactionCurrency $currency */
        foreach ($response as $currency) {
            $result[$currency->id] = sprintf('%s (%s)', $currency->name, $currency->code);
        }

        return $result;
    }
}

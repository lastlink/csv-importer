<?php
declare(strict_types=1);
/**
 * Bills.php
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
use GrumpyDictator\FFIIIApiSupport\Model\Bill;
use GrumpyDictator\FFIIIApiSupport\Request\GetBillsRequest;

/**
 * Class Bills
 */
class Bills implements MapperInterface
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
        $request  = new GetBillsRequest($uri, $token);

        $request->setVerify(config('csv_importer.connection.verify'));
        $request->setTimeOut(config('csv_importer.connection.timeout'));

        $response = $request->get();
        /** @var Bill $bill */
        foreach ($response as $bill) {
            $result[$bill->id] = sprintf('%s (%s)', $bill->name, $bill->repeat_freq);
        }

        return $result;
    }
}

<?php
declare(strict_types=1);
/**
 * MapperService.php
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


use App\Services\CSV\Specifics\SpecificService;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use Log;
use RuntimeException;

/**
 * Class MapperService
 */
class MapperService
{

    /**
     * Appends the given array with data from the CSV file in the config.
     *
     * @param string $content
     * @param bool   $hasHeaders
     * @param array  $specifics
     * @param array  $data
     *
     * @return array
     */
    public static function getMapData(string $content, bool $hasHeaders, array $specifics, array $data): array
    {
        // make file reader first.
        $reader = Reader::createFromString($content);
        $offset = 0;
        if (true === $hasHeaders) {
            $offset = 1;
        }
        try {
            $stmt    = (new Statement)->offset($offset);
            $records = $stmt->process($reader);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new RuntimeException($e->getMessage());
        }

        // loop each row, apply specific:
        foreach ($records as $rowIndex => $row) {
            $row = SpecificService::runSpecifics($row, $specifics);

            // loop each column, put in $data
            foreach ($row as $columnIndex => $column) {
                if(!isset($data[$columnIndex])) {
                    // don't need to handle this. Continue.
                    continue;
                }
                if('' !== $column) {
                    $data[$columnIndex]['values'][] = trim($column);
                }
            }
        }
        // loop data, clean up data:
        foreach($data as $index => $columnInfo) {
            $data[$index]['values'] = array_unique($data[$index]['values']);
            asort($data[$index]['values']);
        }
        return $data;
    }

}

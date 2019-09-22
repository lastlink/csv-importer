<?php
/**
 * RoleService.php
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

namespace App\Services\CSV\Roles;

use App\Services\CSV\Specifics\SpecificService;
use FireflyIII\Exceptions\FireflyException;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use Log;
use RuntimeException;

/**
 * Class RoleService
 */
class RoleService
{
    /**
     * @param string $content
     * @param bool   $hasHeaders
     *
     * @return array
     */
    public static function getColumns(string $content, bool $hasHeaders): array
    {
        $reader  = Reader::createFromString($content);
        $headers = [];
        if (true === $hasHeaders) {
            try {
                $stmt    = (new Statement)->limit(1)->offset(0);
                $records = $stmt->process($reader);
                $headers = $records->fetchOne();
                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new RuntimeException($e->getMessage());
            }
            // @codeCoverageIgnoreEnd
            Log::debug('Detected file headers:', $headers);
        }
        if (false === $hasHeaders) {
            try {
                $stmt    = (new Statement)->limit(1)->offset(0);
                $records = $stmt->process($reader);
                $count   = count($records->fetchOne());
                for ($i = 0; $i < $count; $i++) {
                    $headers[] = sprintf('Column #%d', $i + 1);
                }

                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new RuntimeException($e->getMessage());
            }
        }

        return $headers;
    }

    /**
     * @param string $content
     * @param bool   $hasHeaders
     *
     * @return array
     */
    public static function getExampleData(string $content, bool $hasHeaders, array $specifics): array
    {
        $reader   = Reader::createFromString($content);
        $offset   = $hasHeaders ? 1 : 0;
        $examples = [];
        // make statement.
        try {
            $stmt = (new Statement)->limit(10)->offset($offset);
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new RuntimeException($e->getMessage());
        }
        // @codeCoverageIgnoreEnd

        // grab the records:
        $records = $stmt->process($reader);
        /** @var array $line */
        foreach ($records as $line) {
            $line = array_values($line);
            $line = SpecificService::runSpecifics($line, $specifics);
            foreach ($line as $index => $cell) {
                $examples[$index][] = $cell;
                $examples[$index]   = array_unique($examples[$index]);
            }
        }

        return $examples;
    }

}

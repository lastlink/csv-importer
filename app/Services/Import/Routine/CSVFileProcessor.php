<?php
/**
 * CSVFileProcessor.php
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

use App\Services\CSV\Specifics\SpecificService;
use App\Services\Import\Support\ProgressInformation;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\ResultSet;
use League\Csv\Statement;
use Log;
use RuntimeException;

/**
 * Class CSVFileProcessor
 */
class CSVFileProcessor
{
    use ProgressInformation;

    /** @var bool */
    private $hasHeaders;
    /** @var Reader */
    private $reader;
    /** @var array */
    private $specifics;

    /**
     * CSVFileProcessor constructor.
     */
    public function __construct()
    {
        $this->specifics = [];
    }

    /**
     * Get a reader, and start looping over each line.
     *
     * @return array
     */
    public function processCSVFile(): array
    {
        Log::debug('Now in startImportLoop()');
        $offset = $this->hasHeaders ? 1 : 0;

        Log::debug(sprintf('Offset is %d', $offset));
        try {
            $stmt    = (new Statement)->offset($offset);
            $records = $stmt->process($this->reader);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new RuntimeException($e->getMessage());
        }

        return $this->processCSVLines($records);
    }

    /**
     * @param bool $hasHeaders
     */
    public function setHasHeaders(bool $hasHeaders): void
    {
        $this->hasHeaders = $hasHeaders;
    }

    /**
     * @param Reader $reader
     */
    public function setReader(Reader $reader): void
    {
        $this->reader = $reader;
    }

    /**
     * @param array $specifics
     */
    public function setSpecifics(array $specifics): void
    {
        $this->specifics = $specifics;
    }

    /**
     * Loop all records from CSV file.
     *
     * @param ResultSet $records
     *
     * @return array
     */
    private function processCSVLines(ResultSet $records): array
    {
        $updatedRecords = [];
        $count          = $records->count();
        Log::debug(sprintf('Now in processCSVLines() with %d records', $count));
        $currentIndex = 1;
        $zeroIndex = 0;
        foreach ($records as $index => $line) {
            $line = $this->sanitize($line);
            Log::debug(sprintf('In loop %d/%d', $currentIndex, $count));
            $line             = SpecificService::runSpecifics($line, $this->specifics);
            $updatedRecords[] = $line;
            //            $this->addWarning($zeroIndex,'Warning from CSV file processor.');
            //            $this->addMessage($zeroIndex,'Message from CSV file processor.');
            //            $this->addError($zeroIndex,'Error from CSV file processor.');
            $currentIndex++;
            $zeroIndex++;

        }

        return $updatedRecords;
    }


    /**
     * Do a first sanity check on whatever comes out of the CSV file.
     *
     * @param array $line
     *
     * @return array
     */
    private function sanitize(array $line): array
    {
        $lineValues = array_values($line);
        array_walk(
            $lineValues, static function ($element) {
            $element = trim(str_replace('&nbsp;', ' ', (string)$element));

            return $element;
        }
        );

        return $lineValues;
    }

}
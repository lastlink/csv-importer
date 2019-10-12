<?php
/**
 * PseudoTransactionProcessor.php
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

use App\Services\Import\Task\TaskInterface;
use Log;

/**
 * Class PseudoTransactionProcessor
 */
class PseudoTransactionProcessor
{
    /** @var array */
    private $tasks;

    /**
     * PseudoTransactionProcessor constructor.
     */
    public function __construct()
    {
        $this->tasks = config('csv_importer.transaction_tasks');
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    public function processPseudo(array $lines): array
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        $processed = [];
        /** @var array $line */
        foreach ($lines as $line) {
            $processed[] = $this->processPseudoLine($line);
        }

        return $processed;

    }

    /**
     * @param array $line
     *
     * @return array
     */
    private function processPseudoLine(array $line): array
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        foreach ($this->tasks as $task) {
            /** @var TaskInterface $object */
            $object = app($task);
            $line   = $object->process($line);
        }
        var_dump($line);
        exit;

        return $line;
    }

}
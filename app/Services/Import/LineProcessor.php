<?php
/**
 * LineProcessor.php
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

use Log;
use RuntimeException;

/**
 * Class LineProcessor
 *
 * Processes single lines from a CSV file.
 */
class LineProcessor
{
    /** @var array */
    private $doMapping;
    /** @var array */
    private $mappedValues;
    /** @var array */
    private $mapping;
    /** @var array */
    private $roles;

    public function __construct(array $roles, array $mapping, array $doMapping)
    {
        Log::debug('Created LineProcessor()');
        $this->roles     = $roles;
        $this->mapping   = $mapping;
        $this->doMapping = $doMapping;
    }

    /**
     * Convert each raw CSV to a set of ColumnValue objects, which hold as much info
     * as we can cram into it. These new lines can be imported later on.
     *
     * @param array $line
     *
     * @return array
     */
    public function process(array $line): array
    {
        Log::debug('Now in process()');
        $convertedLine = $this->convertLine($line);

        // should return line.

        return $convertedLine;
    }

    /**
     * Convert each line.
     *
     * @param array $line
     *
     * @return array
     */
    private function convertLine(array $line): array
    {
        $count  = count($line);
        $return = [];
        foreach ($line as $columnIndex => $value) {
            Log::debug(sprintf('Now at column %d/%d', $columnIndex + 1, $count));
            $value        = trim($value);
            $originalRole = $this->roles[$columnIndex] ?? '_ignore';
            Log::debug(sprintf('Now at column #%d (%s), value "%s"', $columnIndex + 1, $originalRole, $value));
            if ('_ignore' === $originalRole) {
                Log::debug(sprintf('Ignore column #%d because role is "_ignore".', $columnIndex + 1));
                continue;
            }
            if ('' === $value) {
                Log::debug(sprintf('Ignore column #%d because value is "".', $columnIndex + 1));
            }

            // is a mapped value present?
            $mapped = $this->mapping[$columnIndex][$value] ?? 0;
            // the role might change because of the mapping.
            $role = $this->getRoleForColumn($columnIndex, $mapped);

            $columnValue = new ColumnValue;
            $columnValue->setValue($value);
            $columnValue->setRole($role);
            $columnValue->setMappedValue($mapped);
            $columnValue->setOriginalRole($originalRole);
            $return[] = $columnValue;
        }
        // add a special column value for the "source"
        $columnValue = new ColumnValue;
        $columnValue->setValue(sprintf('jc5-csv-import-v%s', config('csv_importer.version')));
        $columnValue->setMappedValue(0);
        $columnValue->setRole('original-source');
        $return[] = $columnValue;

        return $return;
    }

    /**
     * If the value in the column is mapped to a certain ID,
     * the column where this ID must be placed will change.
     *
     * For example, if you map role "budget-name" with value "groceries" to 1,
     * then that should become the budget-id. Not the name.
     *
     * @param int $column
     * @param int $mapped
     *
     * @return string
     * @throws RuntimeException
     */
    private function getRoleForColumn(int $column, int $mapped): string
    {
        $role = $this->roles[$column] ?? '_ignore';
        if (0 === $mapped) {
            Log::debug(sprintf('Column #%d with role "%s" is not mapped.', $column, $role));

            return $role;
        }
        if (!(isset($this->doMapping[$column]) && true === $this->doMapping[$column])) {

            // if the mapping has been filled in already by a role with a higher priority,
            // ignore the mapping.
            Log::debug(sprintf('Column #%d ("%s") has something already.', $column, $role));


            return $role;
        }
        $roleMapping = [
            'account-id'            => 'account-id',
            'account-name'          => 'account-id',
            'account-iban'          => 'account-id',
            'account-number'        => 'account-id',
            'bill-id'               => 'bill-id',
            'bill-name'             => 'bill-id',
            'budget-id'             => 'budget-id',
            'budget-name'           => 'budget-id',
            'currency-id'           => 'currency-id',
            'currency-name'         => 'currency-id',
            'currency-code'         => 'currency-id',
            'currency-symbol'       => 'currency-id',
            'category-id'           => 'category-id',
            'category-name'         => 'category-id',
            'foreign-currency-id'   => 'foreign-currency-id',
            'foreign-currency-code' => 'foreign-currency-id',
            'opposing-id'           => 'opposing-id',
            'opposing-name'         => 'opposing-id',
            'opposing-iban'         => 'opposing-id',
            'opposing-number'       => 'opposing-id',
        ];
        if (!isset($roleMapping[$role])) {
            throw new RunTimeException(sprintf('Cannot indicate new role for mapped role "%s"', $role)); // @codeCoverageIgnore
        }
        $newRole = $roleMapping[$role];
        if ($newRole !== $role) {
            Log::debug(sprintf('Role was "%s", but because of mapping (mapped to #%d), role becomes "%s"', $role, $mapped, $newRole));
        }

        // also store the $mapped values in a "mappedValues" array.
        // used to validate whatever has been set as mapping
        // TODO this validation is not implemented yet.
        $this->mappedValues[$newRole][] = $mapped;
        Log::debug(sprintf('Values mapped to role "%s" are: ', $newRole), $this->mappedValues[$newRole]);

        return $newRole;
    }
}

<?php
/**
 * AppendHash.php
 * Copyright (c) 2019 https://github.com/viraptor
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
declare(strict_types=1);

namespace App\Services\CSV\Specifics;

/**
 * Class AppendHash.
 *
 * Appends a column with a consistent hash for duplicate transactions.
 */
class AppendHash implements SpecificInterface
{
    /** @var array Counter for each line. */
    public $lines_counter = array();

    /**
     * Description of the current specific.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getDescription(): string
    {
        return 'specifics.hash_descr';
    }

    /**
     * Name of the current specific.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public static function getName(): string
    {
        return 'specifics.hash_name';
    }

    /**
     * Run the specific code.
     *
     * @param array $row
     *
     * @return array
     *
     */
    public function run(array $row): array
    {
        $representation = implode(',', array_values($row));
        if (array_key_exists($representation, $this->lines_counter)) {
            ++$this->lines_counter[$representation];
        }
        if (!array_key_exists($representation, $this->lines_counter)) {
            $this->lines_counter[$representation] = 1;
        }
        $to_hash = sprintf('%s,%s', $representation, $this->lines_counter[$representation]);

        $row[] = hash('sha256', $to_hash);

        return $row;
    }
}

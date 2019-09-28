<?php
/**
 * SpecificInterface.php
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
declare(strict_types=1);

namespace App\Services\CSV\Specifics;

/**
 * Interface SpecificInterface.
 */
interface SpecificInterface
{
    /**
     * Get description.
     *
     * @return string
     */
    public static function getDescription(): string;

    /**
     * If the fix(es) in your file add or remove columns from the CSV content, this must be reflected on the header row
     * as well.
     *
     * @param array $headers
     *
     * @return array
     */
    public function runOnHeaders(array $headers): array;

    /**
     * Get name.
     *
     * @return string
     */
    public static function getName(): string;

    /**
     * Run the fix(es) on a single line of CSV file.
     *
     * @param array $row
     *
     * @return array
     */
    public function run(array $row): array;
}

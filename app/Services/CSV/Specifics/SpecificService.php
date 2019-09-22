<?php
/**
 * SpecificService.php
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

namespace App\Services\CSV\Specifics;


/**
 * Class SpecificService
 */
class SpecificService
{
    /**
     * @return array
     */
    public static function getSpecifics(): array
    {
        $specifics = [];
        foreach (config('csv_importer.specifics') as $class) {
            if (class_exists($class)) {
                $parts           = explode('\\', $class);
                $key             = $parts[count($parts) - 1];
                $specifics[$key] = [
                    'name'        => trans($class::getName()),
                    'description' => trans($class::getDescription()),
                ];
            }
        }

        return $specifics;
    }

    /**
     * @param array $row
     * @param array $specifics
     *
     * @return array
     */
    public static function runSpecifics(array $row, array $specifics): array
    {
        foreach ($specifics as $name) {
            $class = sprintf('App\\Services\\CSV\\Specifics\\%s', $name);
            if (class_exists($class)) {
                /** @var SpecificInterface $object */
                $object = app($class);
                $row    = $object->run($row);
            }
        }
        return $row;
    }

}

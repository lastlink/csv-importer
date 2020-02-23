<?php
/**
 * ConverterService.php
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

namespace App\Services\CSV\Converter;

use RuntimeException;
use Log;

/**
 * Class ConverterService
 */
class ConverterService
{
    /**
     * @param string      $class
     * @param mixed       $value
     * @param string|null $configuration
     *
     * @return mixed
     */
    public static function convert(string $class, $value, ?string $configuration)
    {
        if ('' === $class) {
            return $value;
        }
        if (self::exists($class)) {
            /** @var ConverterInterface $object */
            $object = app(self::fullName($class));
            Log::debug(sprintf('Created converter class %s', $class));
            if (null !== $configuration) {
                $object->setConfiguration($configuration);
            }

            return $object->convert($value);
        }
        throw new RuntimeException(sprintf('No such converter: "%s"', $class));
    }

    public static function exists(string $class): bool
    {
        $name = self::fullName($class);

        return class_exists($name);
    }

    public static function fullName(string $class): string
    {
        return sprintf('App\\Services\\CSV\\Converter\\%s', $class);
    }

}

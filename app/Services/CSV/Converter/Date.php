<?php
declare(strict_types=1);
/**
 * Date.php
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

namespace App\Services\CSV\Converter;

use App\Exceptions\ImportException;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use Log;

/**
 * Class Date
 */
class Date implements ConverterInterface
{
    private $dateFormat = 'Y-m-d';

    /**
     * Convert a value.
     *
     * @param $value
     *
     * @return mixed
     *
     */
    public function convert($value)
    {
        $string = app('steam')->cleanStringAndNewlines($value);

        /** @var Carbon $carbon */

        if ('' === $string) {
            Log::warning('Empty date string, so date is set to today.');
            $carbon = Carbon::today();
        }
        if ('' !== $string) {
            Log::debug(sprintf('Date converter is going to work on "%s" using format "%s"', $string, $this->dateFormat));
            try {
                $carbon = Carbon::createFromFormat($this->dateFormat, $string);
            } catch (InvalidArgumentException|Exception $e) {
                Log::error(sprintf('%s converting the date: %s', get_class($e), $e->getMessage()));
                return Carbon::today()->format('Y-m-d');
            }
        }

        return $carbon->format('Y-m-d');
    }

    /**
     * Add extra configuration parameters.
     *
     * @param string $configuration
     */
    public function setConfiguration(string $configuration): void
    {
        $this->dateFormat = $configuration;
    }
}

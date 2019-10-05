<?php
/**
 * ColumnValue.php
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

namespace App\Services\Import;

use App\Services\CSV\Converter\ConverterService;

/**
 * Class ColumnValue
 */
class ColumnValue
{
    /** @var int */
    private $mappedValue;
    /** @var string */
    private $originalRole;
    /** @var string */
    private $role;
    /** @var string */
    private $value;

    /**
     * ColumnValue constructor.
     */
    public function __construct()
    {
        $this->mappedValue = 0;
    }

    /**
     * @return int
     */
    public function getMappedValue(): int
    {
        return $this->mappedValue;
    }

    /**
     * @param int $mappedValue
     */
    public function setMappedValue(int $mappedValue): void
    {
        $this->mappedValue = $mappedValue;
    }

    /**
     * @return string
     */
    public function getOriginalRole(): string
    {
        return $this->originalRole;
    }

    /**
     * @param string $originalRole
     */
    public function setOriginalRole(string $originalRole): void
    {
        $this->originalRole = $originalRole;
    }

    /**
     * @return mixed
     */
    public function getParsedValue()
    {
        if (0 !== $this->mappedValue) {
            return $this->mappedValue;
        }

        // run converter on data:
        $converterClass = (string)config(sprintf('csv_importer.import_roles.%s.converter', $this->role));

        return ConverterService::convert($converterClass, $this->value);
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }


}

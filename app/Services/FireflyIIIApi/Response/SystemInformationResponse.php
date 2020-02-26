<?php
/**
 * SystemInformationResponse.php
 * Copyright (c) 2019 james@firefly-iii.org
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

namespace App\Services\FireflyIIIApi\Response;


/**
 * Class SystemInformationResponse
 */
class SystemInformationResponse extends Response
{
    /** @var string */
    public $apiVersion;
    /** @var string */
    public $driver;
    /** @var string */
    public $operatingSystem;
    /** @var string */
    public $phpVersion;
    /** @var string */
    public $version;

    /**
     * Response constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->apiVersion      = $data['api_version'];
        $this->driver          = $data['driver'];
        $this->operatingSystem = $data['os'];
        $this->phpVersion      = $data['php_version'];
        $this->version         = $data['version'];
    }
}

<?php
/**
 * GetPreferenceRequest.php
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

namespace App\Services\FireflyIIIApi\Request;


use App\Exceptions\ApiException;
use App\Exceptions\ApiHttpException;
use App\Services\FireflyIIIApi\Response\PreferenceResponse;
use App\Services\FireflyIIIApi\Response\Response;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class GetPreferenceRequest
 *
 * Returns a single preference.
 */
class GetPreferenceRequest extends Request
{
    /** @var string */
    private $name;

    /**
     * GetBudgetsRequest constructor.
     */
    public function __construct()
    {
        $url   = config('csv_importer.uri');
        $token = config('csv_importer.access_token');
        $this->setBase($url);
        $this->setToken($token);
        $this->setUri('preferences');
    }

    /**
     * @return Response
     * @throws ApiHttpException
     */
    public function get(): Response
    {
        try {
            $data = $this->authenticatedGet();
        } catch (ApiException|GuzzleException $e) {
            throw new ApiHttpException($e->getMessage());
        }

        return new PreferenceResponse($data['data']);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->setUri(sprintf('preferences/%s', $name));
    }

    /**
     * @return Response
     */
    public function post(): Response
    {
        // TODO: Implement post() method.
    }
}

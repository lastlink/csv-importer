<?php
/**
 * GetSearchAccountRequest.php
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
use App\Services\FireflyIIIApi\Response\GetAccountsResponse;
use App\Services\FireflyIIIApi\Response\Response;
use GuzzleHttp\Exception\GuzzleException;
use Log;
/**
 * Class GetSearchAccountRequest
 */
class GetSearchAccountRequest extends Request
{
    /** @var string */
    private $query;
    /** @var string */
    private $field;

    /**
     * GetSearchAccountRequest constructor.
     */
    public function __construct()
    {
        $url        = config('csv_importer.uri');
        $token      = config('csv_importer.access_token');
        $this->setBase($url);
        $this->setToken($token);
        $this->setUri('search/accounts');
    }

    /**
     * @return Response
     * @throws ApiHttpException
     */
    public function get(): Response
    {
        $collectedRows = [];
        $hasNextPage   = true;
        $loopCount     = 0;
        $page          = 1;
        Log::debug(sprintf('Start of %s', __METHOD__));

        while ($hasNextPage && $loopCount < 30) {
            $parameters = $this->getParameters();
            $parameters['page'] = $page;
            $this->setParameters($parameters);
            try {
                $data = $this->authenticatedGet();
            } catch (ApiException|GuzzleException $e) {
                throw new ApiHttpException($e->getMessage());
            }
            $collectedRows[] = $data['data'];
            $totalPages      = $data['meta']['pagination']['total_pages'] ?? 1;
            if ($page < $totalPages) {
                $page++;
                continue;
            }
            if ($page >= $totalPages) {
                $hasNextPage = false;
                continue;
            }
        }
        return new GetAccountsResponse(array_merge(...$collectedRows));
    }

    /**
     * @return string
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
        $this->setParameters(['query' => $query, 'field' => $this->getField()]);
    }

    /**
     * @return string
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField(string $field): void
    {
        $this->field = $field;
        $this->setParameters(['query' => $this->getQuery(), 'field' => $field]);
    }


    /**
     * @return Response
     */
    public function post(): Response
    {
        // TODO: Implement post() method.
    }
}

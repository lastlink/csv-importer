<?php
/**
 * GetBillsRequest.php
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

namespace App\Services\FireflyIIIApi\Request;


use App\Exceptions\ApiException;
use App\Exceptions\ApiHttpException;
use App\Services\FireflyIIIApi\Response\GetBillsResponse;
use App\Services\FireflyIIIApi\Response\Response;
use GuzzleHttp\Exception\GuzzleException;
use Log;

/**
 * Class GetBillsRequest.
 */
class GetBillsRequest extends Request
{
    /**
     * GetAccountsRequest constructor.
     */
    public function __construct()
    {
        $url        = config('csv_importer.uri');
        $token      = config('csv_importer.access_token');
        $this->setBase($url);
        $this->setToken($token);
        $this->setUri('bills');
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
            $parameters         = $this->getParameters();
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

        return new GetBillsResponse(array_merge(...$collectedRows));

    }

    /**
     * @return Response
     */
    public function post(): Response
    {
        // TODO: Implement post() method.
    }
}

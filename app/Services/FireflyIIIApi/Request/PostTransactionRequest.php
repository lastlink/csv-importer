<?php
/**
 * PostTransactionRequest.php
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
use App\Services\FireflyIIIApi\Response\PostTransactionResponse;
use App\Services\FireflyIIIApi\Response\Response;
use App\Services\FireflyIIIApi\Response\ValidationErrorResponse;
use GuzzleHttp\Exception\GuzzleException;
use Log;

/**
 * Class PostTransactionRequest
 * POST a transaction.
 */
class PostTransactionRequest extends Request
{
    /**
     * PostTransactionRequest constructor.
     */
    public function __construct()
    {
        $url        = config('csv_importer.uri');
        $token      = config('csv_importer.access_token');
        $this->setBase($url);
        $this->setToken($token);
        $this->setUri('transactions');
    }

    /**
     * @return Response
     */
    public function get(): Response
    {
        // TODO: Implement get() method.
    }

    /**
     * @return Response
     * @throws ApiHttpException
     */
    public function post(): Response
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        try {
            $data = $this->authenticatedPost();
        } catch (ApiException|GuzzleException $e) {
            throw new ApiHttpException($e->getMessage());
        }
        if(isset($data['message']) && self::VALIDATION_ERROR_MSG === $data['message']) {
            return new ValidationErrorResponse($data['errors']);
        }

        return new PostTransactionResponse($data['data']);
    }
}

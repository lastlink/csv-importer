<?php
/**
 * Request.php
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
use App\Services\FireflyIIIApi\Response\Response;
use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Log;

/**
 * Class Request
 */
abstract class Request
{
    /** @var string */
    private $base;
    /** @var string */
    private $token;
    /** @var string */
    private $uri;
    /** @var array */
    private $parameters;

    /**
     * @return Response
     * @throws ApiHttpException
     */
    abstract public function get(): Response;

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        $cacheKey = hash('sha256', sprintf('%s-%s-%s-%s', $this->base, $this->token, $this->uri, json_encode($this->parameters)));

        return $cacheKey;
    }


    /**
     * @return array
     * @throws ApiException
     * @throws GuzzleException
     */
    protected function authenticatedGet(): array
    {
        $cacheKey = $this->getCacheKey();
        if (Cache::has($cacheKey)) {
            Log::debug(sprintf('%s is present in cache.', substr($cacheKey, 0, 5)));

            return Cache::get($cacheKey);
        }

        Log::debug(sprintf('%s is NOT present in cache.', substr($cacheKey, 0, 5)));

        $this->freshAuthenticatedGet();
    }

    /**
     * @return array
     * @throws ApiException
     * @throws GuzzleException
     */
    private function freshAuthenticatedGet(): array
    {
        Log::debug('freshAuthenticatedGet()');
        $fullUri  = sprintf('%s/api/v1/%s', $this->getBase(), $this->getUri());
        $cacheKey = $this->getCacheKey();
        if (null !== $this->parameters) {
            $fullUri = sprintf('%s?%s', $fullUri, http_build_query($this->parameters));
        }
        //Log::debug(sprintf('Full URI is %s', $fullUri));
        //Log::debug(sprintf('Now in freshAuthenticatedGet(%s): %s', $cacheKey, $fullUri));

        $client = $this->getClient();
        $res    = $client->request(
            'GET', $fullUri, [
                     'headers' => [
                         'Accept'        => 'application/json',
                         'Authorization' => sprintf('Bearer %s', $this->getToken()),
                     ],
                     'verify'  => resource_path('certs/ca.cert.pem'),
                 ]
        );

        if (200 !== $res->getStatusCode()) {
            throw new ApiException('Status code is %d', $res->getStatusCode());
        }

        $body = $res->getBody();
        $json = json_decode($body, true);

        if (null === $json) {
            throw new ApiException(sprintf('Body is empty. Status code is %d.', $res->getStatusCode()));
        }

        Cache::put($cacheKey, $json, 604800);

        return $json;
    }

    /**
     * @return mixed
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @param mixed $base
     */
    public function setBase($base): void
    {
        $this->base = $base;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @return Client
     */
    private function getClient(): Client
    {
        $client = new Client;

        // config here


        return $client;
    }


}

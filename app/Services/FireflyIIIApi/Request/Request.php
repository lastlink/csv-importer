<?php


namespace App\Services\FireflyIIIApi\Request;

use App\Exceptions\ApiException;
use App\Exceptions\ApiHttpException;
use App\Services\FireflyIIIApi\Response\Response;
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
     * @return array
     * @throws ApiException
     * @throws GuzzleException
     */
    protected function authenticatedGet(): array
    {
        Log::debug('Now in authenticatedGet()');
        $fullUri = sprintf('%s/api/v1/%s', $this->getBase(), $this->getUri());
        if (null !== $this->parameters) {
            $fullUri = sprintf('%s?%s', $fullUri, http_build_query($this->parameters));
        }
        Log::debug(sprintf('Full URI is %s', $fullUri));

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
        // do something with body.

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

<?php


namespace App\Services\FireflyIIIApi\Request;

use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Request
 */
class Request
{
    /** @var string */
    private $base;
    /** @var string */
    private $token;
    /** @var string */
    private $uri;

    /**
     * @return array
     * @throws ApiException
     * @throws GuzzleException
     */
    public function authenticatedGet(): array
    {
        $fullUri = sprintf('%s/api/v1/%s', $this->getBase(), $this->getUri());
        $client  = $this->getClient();

        $res = $client->request(
            'GET', $fullUri, [
                     'headers' => [
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

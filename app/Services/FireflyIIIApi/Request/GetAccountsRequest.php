<?php


namespace App\Services\FireflyIIIApi\Request;


use App\Exceptions\ApiException;
use App\Exceptions\ApiHttpException;
use App\Services\FireflyIIIApi\Response\GetAccountsResponse;
use App\Services\FireflyIIIApi\Response\Response;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class GetAccountsRequest
 */
class GetAccountsRequest extends Request
{
    /** @var string */
    public const ASSET = 'asset';
    /** @var string */
    private $type;


    /**
     * GetAccountsRequest constructor.
     */
    public function __construct()
    {
        $url        = config('csv_importer.uri');
        $token      = config('csv_importer.access_token');
        $this->type = 'all';
        $this->setBase($url);
        $this->setToken($token);
        $this->setUri('accounts');
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
        $response = new GetAccountsResponse($data['data']);

        return $response;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
        $this->setParameters(['type' => $type]);
    }

}

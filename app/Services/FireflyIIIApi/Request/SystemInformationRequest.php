<?php


namespace App\Services\FireflyIIIApi\Request;


use App\Exceptions\ApiException;
use App\Exceptions\ApiHttpException;
use App\Services\FireflyIIIApi\Response\Response;
use App\Services\FireflyIIIApi\Response\SystemInformationResponse;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class SystemInformationRequest
 */
class SystemInformationRequest extends Request
{
    /**
     * SystemInformationRequest constructor.
     */
    public function __construct()
    {
        $url   = config('csv_importer.uri');
        $token = config('csv_importer.access_token');

        $this->setBase($url);
        $this->setToken($token);
        $this->setUri('about');
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
        return new SystemInformationResponse($data['data'] ?? []);
    }
}

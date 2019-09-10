<?php


namespace App\Services\FireflyIIIApi\Request;


/**
 * Class SystemInformationRequest
 */
class SystemInformationRequest extends Request
{
    public function __construct()
    {
        $url   = config('csv_importer.uri');
        $token = config('csv_importer.access_token');

        $this->setBase($url);
        $this->setToken($token);
        $this->setUri('about');
    }


}

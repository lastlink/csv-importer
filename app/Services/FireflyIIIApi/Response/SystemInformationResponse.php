<?php


namespace App\Services\FireflyIIIApi\Response;


/**
 * Class SystemInformationResponse
 */
class SystemInformationResponse extends Response
{
    /** @var string */
    public $apiVersion;
    /** @var string */
    public $driver;
    /** @var string */
    public $operatingSystem;
    /** @var string */
    public $phpVersion;
    /** @var string */
    public $version;

    /**
     * Response constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->apiVersion      = $data['api_version'];
        $this->driver          = $data['driver'];
        $this->operatingSystem = $data['os'];
        $this->phpVersion      = $data['php_version'];
        $this->version         = $data['version'];
    }
}

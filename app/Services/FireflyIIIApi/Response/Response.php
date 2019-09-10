<?php


namespace App\Services\FireflyIIIApi\Response;

/**
 * Class Response
 */
abstract class Response
{
    /**
     * Response constructor.
     *
     * @param array $data
     */
    abstract public function __construct(array $data);

}

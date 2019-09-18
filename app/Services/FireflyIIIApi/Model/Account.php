<?php


namespace App\Services\FireflyIIIApi\Model;


class Account
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $type;

    /**
     * Account constructor.
     */
    protected function __construct()
    {

    }

    /**
     * @param array $array
     *
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $account = new Account;

        $account->id   = (int)$array['id'];
        $account->name = $array['attributes']['name'];
        $account->type = $array['attributes']['type'];

        return $account;

    }

}

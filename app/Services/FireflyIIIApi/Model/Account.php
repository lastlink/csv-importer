<?php
/**
 * Account.php
 * Copyright (c) 2019 james@firefly-iii.org
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

namespace App\Services\FireflyIIIApi\Model;

/**
 * Class Account
 */
class Account
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $type;
    /** @var string */
    public $iban;
    /** @var string */
    public $number;
    /** @var string */
    public $bic;

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
        $account         = new Account;
        $account->id     = (int)$array['id'];
        $account->name   = $array['attributes']['name'];
        $account->type   = $array['attributes']['type'];
        $account->iban   = $array['attributes']['iban'];
        $account->number = $array['attributes']['account_number'];
        $account->bic    = $array['attributes']['bic'];

        return $account;

    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'type'   => $this->type,
            'iban'   => $this->iban,
            'number' => $this->number,
            'bic'    => $this->bic,
        ];
    }

}

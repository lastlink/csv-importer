<?php
/**
 * AssetAccountIbans.php
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

namespace App\Services\CSV\Mapper;

use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Request\GetAccountsRequest;

/**
 * Class AssetAccountIbans
 */
class AssetAccountIbans implements MapperInterface
{

    /**
     * TODO very duplicate with AssetAccounts
     * Get map of objects.
     *
     * @return array
     */
    public function getMap(): array
    {
        $result = [];
        // get list of asset accounts:
        $request = new GetAccountsRequest;
        $request->setType(GetAccountsRequest::ASSET);
        $response = $request->get();
        /** @var Account $account */
        foreach ($response as $account) {
            if (null !== $account->iban) {
                $name = sprintf('%s (%s)', $account->iban, $account->name);
                // add optgroup to result:
                $group                        = trans(sprintf('import.account_types_%s', $account->type));
                $result[$group]               = $result[$group] ?? [];
                $result[$group][$account->id] = $name;
            }

        }

        // get list of liabilities.
        $request = new GetAccountsRequest;
        $request->setType(GetAccountsRequest::LIABILITIES);
        $response = $request->get();
        /** @var Account $account */
        foreach ($response as $account) {
            if (null !== $account->iban) {
                $name = sprintf('%s (%s)', $account->iban, $account->name);
                // add optgroup to result:
                $group                        = trans(sprintf('import.account_types_%s', $account->type));
                $result[$group]               = $result[$group] ?? [];
                $result[$group][$account->id] = $name;
            }
        }

        return $result;
    }
}

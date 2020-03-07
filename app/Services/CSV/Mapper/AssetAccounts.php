<?php
/**
 * AssetAccounts.php
 * Copyright (c) 2020 james@firefly-iii.org
 *
 * This file is part of the Firefly III CSV importer
 * (https://github.com/firefly-iii/csv-importer).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\CSV\Mapper;

use App\Services\FireflyIIIApi\Model\Account;
use App\Services\FireflyIIIApi\Request\GetAccountsRequest;

/**
 * Class AssetAccounts
 */
class AssetAccounts implements MapperInterface
{

    /**
     * TODO duplicate code here.
     * Get map of objects.
     *
     * @return array
     * @throws \App\Exceptions\ApiHttpException
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
            $name = $account->name;
            if (null !== $account->iban) {
                $name = sprintf('%s (%s)', $account->name, $account->iban);
            }
            // add optgroup to result:
            $group                        = trans(sprintf('import.account_types_%s', $account->type));
            $result[$group]               = $result[$group] ?? [];
            $result[$group][$account->id] = $name;
        }

        // get list of liabilities.
        $request = new GetAccountsRequest;
        $request->setType(GetAccountsRequest::LIABILITIES);
        $response = $request->get();
        /** @var Account $account */
        foreach ($response as $account) {
            $name = $account->name;
            if (null !== $account->iban) {
                $name = sprintf('%s (%s)', $account->name, $account->iban);
            }
            // add optgroup to result:
            $group                        = trans(sprintf('import.account_types_%s', $account->type));
            $result[$group]               = $result[$group] ?? [];
            $result[$group][$account->id] = $name;
        }

        return $result;
    }
}

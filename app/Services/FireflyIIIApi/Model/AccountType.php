<?php
/**
 * AccountType.php
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

namespace App\Services\FireflyIIIApi\Model;

/**
 * Class AccountType
 */
class AccountType
{
    /** @var string */
    public const ASSET = 'asset';

    /** @var string */
    public const CASH = 'cash';

    /** @var string */
    public const DEBT = 'debt';

    /** @var string */
    public const LOAN = 'loan';

    /** @var string */
    public const EXPENSE = 'expense';

    /** @var string */
    public const INITIAL_BALANCE = 'initial balance';

    /** @var string */
    public const MORTGAGE = 'mortgage';

    /** @var string */
    public const RECONCILIATION = 'reconciliation';

    /** @var string */
    public const REVENUE = 'revenue';

    public const LIABILITIES = 'liabilities';
}

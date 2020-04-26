<?php
/**
 * NavController.php
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

namespace App\Http\Controllers;

use App\Services\Session\Constants;

/**
 * Class NavController
 */
class NavController extends Controller
{
    /**
     * Return back to upload.
     */
    public function toConfig()
    {
        session()->forget(Constants::CONFIG_COMPLETE_INDICATOR);

        return redirect(route('import.configure.index') . '?overruleskip=true');
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toRoles()
    {
        session()->forget(Constants::ROLES_COMPLETE_INDICATOR);
        return redirect(route('import.roles.index'));
    }

    /**
     * Return back to index. Needs no session updates.
     */
    public function toStart()
    {
        return redirect(route('index'));
    }

    /**
     * Return back to upload.
     */
    public function toUpload()
    {
        session()->forget(Constants::HAS_UPLOAD);

        return redirect(route('import.start'));
    }

}

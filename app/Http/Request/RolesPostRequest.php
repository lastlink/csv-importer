<?php
declare(strict_types=1);
/**
 * RolesPostRequest.php
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

namespace App\Http\Request;


/**
 * Class RolesPostRequest
 */
class RolesPostRequest extends Request
{

    /**
     * Verify the request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $data = [
            'roles'      => $this->get('roles') ?? [],
            'do_mapping' => $this->get('do_mapping') ?? [],
        ];
        foreach (array_keys($data['roles']) as $index) {

            $data['do_mapping'][$index] = $this->convertBoolean($data['do_mapping'][$index] ?? false);
        }

        return $data;
    }


    /**
     * @return array
     */
    public function rules(): array
    {
        $keys = implode(',', array_keys(config('csv_importer.import_roles')));

        return [
            'roles.*'      => sprintf('required|in:%s', $keys),
            'do_mapping.*' => 'numeric|between:0,1',
        ];
    }

}

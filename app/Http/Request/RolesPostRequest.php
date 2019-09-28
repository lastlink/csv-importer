<?php
/**
 * RolesPostRequest.php
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

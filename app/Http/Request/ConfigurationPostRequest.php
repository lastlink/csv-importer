<?php
/**
 * ConfigurationPostRequest.php
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


use App\Services\CSV\Specifics\SpecificService;

/**
 * Class ConfigurationPostRequest
 */
class ConfigurationPostRequest extends Request
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
        $roles     = $this->get('roles') ? json_decode(base64_decode($this->get('roles')), true) : null;
        $mapping   = $this->get('mapping') ? json_decode(base64_decode($this->get('mapping')), true) : null;
        $doMapping = $this->get('do_mapping') ? json_decode(base64_decode($this->get('do_mapping')), true) : null;


        $result = [
            'headers'                       => $this->convertBoolean($this->get('headers')),
            'delimiter'                     => $this->string('delimiter'),
            'date'                          => $this->string('date'),
            'default_account'               => $this->integer('default_account'),
            'rules'                         => $this->convertBoolean($this->get('rules')),
            'ignore_duplicate_lines'        => $this->convertBoolean($this->get('ignore_duplicate_lines')),
            'ignore_duplicate_transactions' => $this->convertBoolean($this->get('ignore_duplicate_transactions')),
            'skip_form'                     => $this->convertBoolean($this->get('skip_form')),
            'specifics'                     => [],
            'roles'                         => $roles,
            'mapping'                       => $mapping,
            'do_mapping'                    => $doMapping,
        ];
        // rules for specifics:
        $specifics = SpecificService::getSpecifics();
        foreach (array_keys($specifics) as $specific) {
            $result['specifics'][$specific] = $this->convertBoolean($this->get(sprintf('specific_%s', $specific)));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            //'some_weird_field' => 'required',
            'headers'                       => 'numeric|between:0,1',
            'delimiter'                     => 'required|in:comma,semicolon,tab',
            'date'                          => 'required|between:1,15',
            'default_account'               => 'required|numeric|min:1|max:100000',
            'rules'                         => 'numeric|between:0,1',
            'ignore_duplicate_lines'        => 'numeric|between:0,1',
            'ignore_duplicate_transactions' => 'numeric|between:0,1',
            'skip_form'                     => 'numeric|between:0,1',
        ];
        // rules for specifics:
        $specifics = SpecificService::getSpecifics();
        foreach (array_keys($specifics) as $specific) {
            $rules[sprintf('specific_%s', $specific)] = 'numeric|between:0,1';
        }

        return $rules;
    }
}

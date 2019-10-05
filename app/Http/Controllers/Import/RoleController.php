<?php
/**
 * RoleController.php
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

namespace App\Http\Controllers\Import;


use App\Http\Controllers\Controller;
use App\Http\Middleware\RolesComplete;
use App\Http\Request\RolesPostRequest;
use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\Roles\RoleService;
use App\Services\Session\Constants;
use App\Services\Storage\StorageService;

/**
 * Class RoleController
 */
class RoleController extends Controller
{
        /**
         * RoleController constructor.
         */
        public function __construct()
        {
            parent::__construct();
            app('view')->share('pageTitle', 'Define roles');
            $this->middleware(RolesComplete::class);
        }

        /**
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         */
    public function index()
    {
        $mainTitle = 'Define roles';
        $subTitle  = 'Configure the role of each column in your file';

        // get configuration object.
        $configuration = Configuration::fromArray(session()->get(Constants::CONFIGURATION));

        // get columns from file
        $content  = StorageService::getContent(session()->get(Constants::UPLOAD_CSV_FILE));
        $columns  = RoleService::getColumns($content, $configuration->isHeaders(), $configuration->getSpecifics());
        $examples = RoleService::getExampleData($content, $configuration->isHeaders(), $configuration->getSpecifics());

        // submit mapping from config.
        $mapping = base64_encode(json_encode($configuration->getMapping()));

        // roles
        $roles = config('csv_importer.import_roles');

        // configuration (if it is set)
        $configuredRoles     = $configuration->getRoles();
        $configuredDoMapping = $configuration->getDoMapping();

        return view(
            'import.roles.index',
            compact('mainTitle', 'subTitle', 'columns', 'examples', 'roles', 'configuredRoles', 'configuredDoMapping', 'mapping')
        );
    }

    /**
     *
     */
    public function postIndex(RolesPostRequest $request)
    {
        $data = $request->getAll();

        // get configuration object.
        $configuration = Configuration::fromArray(session()->get(Constants::CONFIGURATION));
        $configuration->setRoles($data['roles']);
        $configuration->setDoMapping($data['do_mapping']);

        session()->put(Constants::CONFIGURATION, $configuration->toArray());

        // set role config as complete.
        session()->put(Constants::ROLES_COMPLETE_INDICATOR, true);

        // redirect to mapping thing.
        return redirect()->route('import.mapping.index');
    }
}

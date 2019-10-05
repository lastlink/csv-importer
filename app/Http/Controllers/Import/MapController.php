<?php
/**
 * MapController.php
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
use App\Http\Middleware\MappingComplete;
use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\Mapper\MapperInterface;
use App\Services\CSV\Mapper\MapperService;
use App\Services\Session\Constants;
use App\Services\Storage\StorageService;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Class MapController
 */
class MapController extends Controller
{

    /**
     * RoleController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        app('view')->share('pageTitle', 'Map data');
        $this->middleware(MappingComplete::class);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $mainTitle = 'Map data';
        $subTitle  = 'Map values in CSV bla bla.';

        // get configuration object.
        $configuration   = Configuration::fromArray(session()->get(Constants::CONFIGURATION));
        $roles           = $configuration->getRoles();
        $existingMapping = $configuration->getMapping();
        $data            = [];

        foreach ($roles as $index => $role) {
            $info = config('csv_importer.import_roles')[$role] ?? null;
            $mappable = $info['mappable'] ?? false;
            if (null === $info) {
                continue;
            }
            if (false === $mappable) {
                continue;
            }
            $info['role']   = $role;
            $info['values'] = [];


            // create the "mapper" class which will get data from Firefly III.
            // TODO make a service
            $class = sprintf('App\\Services\\CSV\\Mapper\\%s', $info['mapper']);
            if (!class_exists($class)) {
                throw new RunTimeException(sprintf('Class %s does not exist.', $class));
            }
            /** @var MapperInterface $object */
            $object               = app($class);
            $info['mapping_data'] = $object->getMap();

            $info['mapped'] = $existingMapping[$index] ?? [];

            $data[$index] = $info;
        }

        // TODO here we collect all the values from the CSV file.
        // get columns from file
        $content = StorageService::getContent(session()->get(Constants::UPLOAD_CSV_FILE));
        $data    = MapperService::getMapData($content, $configuration->isHeaders(), $configuration->getSpecifics(), $data);

        //        var_dump($data);
        //        exit;


        //        var_dump($configuration);
        //        exit;

        return view('import.map.index', compact('mainTitle', 'subTitle', 'roles', 'data'));
    }

    public function postIndex(Request $request)
    {
        $values  = $request->get('values') ?? [];
        $mapping = $request->get('mapping') ?? [];
        $values  = !is_array($values) ? [] : $values;
        $mapping = !is_array($mapping) ? [] : $mapping;
        $data    = [];
        $configuration   = Configuration::fromArray(session()->get(Constants::CONFIGURATION));



        /**
         * Loop array with available columns.
         *
         * @var int   $index
         * @var array $row
         */
        foreach ($values as $columnIndex => $column) {
            /**
             * Loop all values for this column
             *
             * @var int    $valueIndex
             * @var string $value
             */
            foreach ($column as $valueIndex => $value) {
                $mappedValue = $mapping[$columnIndex][$valueIndex] ?? null;
                if (null !== $mappedValue && 0 !== $mappedValue && '0' !== $mappedValue) {
                    $data[$columnIndex][$value] = (int)$mappedValue;
                }

            }
        }
        $configuration->setMapping($data);

        // store mapping in config object ( + session)
        session()->put(Constants::CONFIGURATION, $configuration->toArray());

        // set map config as complete.
        session()->put(Constants::MAPPING_COMPLETE_INDICATOR, true);

        return redirect()->route('import.run.index');
    }
}

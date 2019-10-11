<?php
/**
 * RunController.php
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
use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\File\FileReader;
use App\Services\Import\ImportJobStatus\ImportJobStatusManager;
use App\Services\Import\ImportRoutineManager;
use App\Services\Session\Constants;
use Illuminate\Http\JsonResponse;

/**
 * Class RunController
 */
class RunController extends Controller
{

    /**
     *
     */
    public function index()
    {
        $mainTitle = 'Importing';
        $subTitle  = 'Import subtitle';

        return view('import.run.index', compact('mainTitle', 'subTitle'));
    }

    /**
     * @return JsonResponse
     */
    public function start(): JsonResponse
    {
        $importJobStatus = ImportJobStatusManager::startOrFindJob();
        // TODO also start job.
        $routine = new ImportRoutineManager;
        $routine->setConfiguration(Configuration::fromArray(session()->get(Constants::CONFIGURATION)));
        $routine->setReader(FileReader::getReaderFromSession());
        $routine->start();


        return response()->json($importJobStatus->toArray());
    }

    public function status(): JsonResponse
    {
        $importJobStatus = ImportJobStatusManager::startOrFindJob();

        return response()->json($importJobStatus->toArray());
    }
}

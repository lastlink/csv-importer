<?php
declare(strict_types=1);
/**
 * RunController.php
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

namespace App\Http\Controllers\Import;


use App\Exceptions\ImportException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ReadyForImport;
use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\File\FileReader;
use App\Services\Import\ImportJobStatus\ImportJobStatus;
use App\Services\Import\ImportJobStatus\ImportJobStatusManager;
use App\Services\Import\ImportRoutineManager;
use App\Services\Session\Constants;
use ErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use TypeError;


/**
 * Class RunController
 */
class RunController extends Controller
{

    /**
     * StartController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        app('view')->share('pageTitle', 'Importing data...');
        $this->middleware(ReadyForImport::class);
    }

    /**
     *
     */
    public function index()
    {
        $mainTitle = 'Import the data';
        $subTitle  = 'Connect to Firefly III and store your data';

        // job ID may be in session:
        $identifier = session()->get(Constants::JOB_IDENTIFIER);
        $routine    = new ImportRoutineManager($identifier);
        $identifier = $routine->getIdentifier();

        Log::debug(sprintf('Import routine manager identifier is "%s"', $identifier));

        // store identifier in session so the status can get it.
        session()->put(Constants::JOB_IDENTIFIER, $identifier);
        Log::debug(sprintf('Stored "%s" under "%s"', $identifier, Constants::JOB_IDENTIFIER));

        return view('import.run.index', compact('mainTitle', 'subTitle', 'identifier'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function start(Request $request): JsonResponse
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        $identifier = $request->get('identifier');
        $routine    = new ImportRoutineManager($identifier);


        $importJobStatus = ImportJobStatusManager::startOrFindJob($identifier);
        ImportJobStatusManager::setJobStatus(ImportJobStatus::JOB_RUNNING);

        try {
            $routine->setConfiguration(Configuration::fromArray(session()->get(Constants::CONFIGURATION)));
            $routine->setReader(FileReader::getReaderFromSession());
            $routine->start();
        } catch (ImportException|ErrorException|TypeError $e) {
            // update job to error state.
            ImportJobStatusManager::setJobStatus(ImportJobStatus::JOB_ERRORED);
            $error = sprintf('Internal error: %s in file %s:%d', $e->getMessage(), $e->getFile(), $e->getLine());
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            ImportJobStatusManager::addError($identifier, 0, $error);

            return response()->json($importJobStatus->toArray());
        }

        // set done:
        ImportJobStatusManager::setJobStatus(ImportJobStatus::JOB_DONE);

        return response()->json($importJobStatus->toArray());
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {

        $identifier = $request->get('identifier');
        Log::debug(sprintf('Now at %s(%s)', __METHOD__, $identifier));
        if (null === $identifier) {
            Log::warning('Identifier is NULL.');
            // no status is known yet because no identifier is in the session.
            // As a fallback, return empty status
            $fakeStatus = new ImportJobStatus;

            return response()->json($fakeStatus->toArray());
        }
        $importJobStatus = ImportJobStatusManager::startOrFindJob($identifier);

        return response()->json($importJobStatus->toArray());
    }
}

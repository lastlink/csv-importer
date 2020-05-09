<?php
declare(strict_types=1);
/**
 * UploadController.php
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
use App\Http\Middleware\UploadedFiles;
use App\Services\CSV\Configuration\ConfigFileProcessor;
use App\Services\Session\Constants;
use App\Services\Storage\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\MessageBag;
use Log;
use Storage;

/**
 * Class UploadController
 */
class UploadController extends Controller
{
    /**
     * UploadController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(UploadedFiles::class);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Redirector
     */
    public function upload(Request $request)
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        $csvFile    = $request->file('csv_file');
        $configFile = $request->file('config_file');
        $errors     = new MessageBag;

        if (null === $csvFile) {
            $errors->add('csv_file', 'No file was uploaded.');

            return redirect(route('import.start'))->withErrors($errors);
        }
        $errorNumber = $csvFile->getError();
        if (0 !== $errorNumber) {
            $errors->add('csv_file', $errorNumber);
        }

        // upload the file to a temp directory and use it from there.
        if (null !== $csvFile && 0 === $errorNumber) {
            $csvFileName = StorageService::storeContent(file_get_contents($csvFile->getPathname()));
            session()->put(Constants::UPLOAD_CSV_FILE, $csvFileName);
            session()->put(Constants::HAS_UPLOAD, 'true');
        }

        // if present, and no errors, upload the config file and store it in the session.
        if (null !== $configFile) {
            Log::debug('Config file is present.');
            $errorNumber = $configFile->getError();
            if (0 !== $errorNumber) {
                $errors->add('config_file', $errorNumber);
            }
            // upload the file to a temp directory and use it from there.
            if (0 === $errorNumber) {
                Log::debug('Config file uploaded.');
                $configFileName = StorageService::storeContent(file_get_contents($configFile->getPathname()));

                session()->put(Constants::UPLOAD_CONFIG_FILE, $configFileName);

                // process the config file
                try {
                    $configuration = ConfigFileProcessor::convertConfigFile($configFileName);
                    session()->put(Constants::CONFIGURATION, $configuration->toArray());
                } catch (ImportException $e) {
                    $errors->add('config_file', $e->getMessage());
                }
            }
        }
        // if no uploaded config file, read and use the submitted existing file, if any.
        $existingFile = $request->get('existing_config');

        if (null === $configFile && '' !== $existingFile) {
            Log::debug('User selected a config file from the store.');
            $disk = Storage::disk('configurations');
            $configFileName = StorageService::storeContent($disk->get($existingFile));

            session()->put(Constants::UPLOAD_CONFIG_FILE, $configFileName);

            // process the config file
            try {
                $configuration = ConfigFileProcessor::convertConfigFile($configFileName);
                session()->put(Constants::CONFIGURATION, $configuration->toArray());
            } catch (ImportException $e) {
                $errors->add('config_file', $e->getMessage());
            }
        }

        if ($errors->count() > 0) {
            return redirect(route('import.start'))->withErrors($errors);
        }

        return redirect(route('import.configure.index'));
    }
}

<?php
/**
 * UploadController.php
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
use App\Http\Middleware\UploadedFiles;
use App\Services\CSV\Configuration\ConfigFileProcessor;
use App\Services\Session\Constants;
use App\Services\Storage\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

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
     *
     */
    public function upload(Request $request)
    {
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
        }

        // if present, and no errors, upload the config file and store it in the session.

        if (null !== $configFile) {
            $errorNumber = $configFile->getError();
            if (0 !== $errorNumber) {
                $errors->add('config_file', $errorNumber);
            }
            // upload the file to a temp directory and use it from there.
            if (0 === $errorNumber) {
                $configFileName = StorageService::storeContent(file_get_contents($configFile->getPathname()));

                session()->put(Constants::UPLOAD_CONFIG_FILE, $configFileName);

                // process the config file
                $configuration = ConfigFileProcessor::convertConfigFile($configFileName);
                session()->put(Constants::CONFIGURATION, $configuration->toArray());
            }
        }

        if ($errors->count() > 0) {
            return redirect(route('import.start'))->withErrors($errors);
        }

        return redirect(route('import.configure.index'));
    }

    /**
     * @param int $error
     *
     * @return string
     */
    private function getError(int $error): string
    {
        $errors = [
            UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk. Introduced in PHP 5.1.0.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];

        return $errors[$error] ?? 'Unknown error';
    }

}

<?php


namespace App\Http\Controllers\Import;


use App\Http\Controllers\Controller;
use App\Services\Storage\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

/**
 * Class UploadController
 */
class UploadController extends Controller
{

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
            session()->put('csv_file_path', $csvFileName);
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

                session()->put('config_file_path', $configFileName);
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

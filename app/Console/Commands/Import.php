<?php

namespace App\Console\Commands;

use App\Exceptions\ApiHttpException;
use App\Exceptions\ImportException;
use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\File\FileReader;
use App\Services\FireflyIIIApi\Request\SystemInformationRequest;
use App\Services\Import\ImportRoutineManager;
use Illuminate\Console\Command;
use JsonException;
use Log;

/**
 * Class Import
 */
class Import extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a CSV file. Requires the CSV file and the associated configuration file.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:import {file} {config}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $access = $this->haveAccess();
        if (false === $access) {
            $this->error('Could not connect to your local Firefly III instance.');

            return 1;
        }

        $this->info(sprintf('Welcome to the Firefly III CSV importer, v%s', config('csv_importer.version')));
        Log::debug(sprintf('Now in %s', __METHOD__));
        $file   = $this->argument('file');
        $config = $this->argument('config');
        if (!file_exists($file) || (file_exists($file) && !is_file($file))) {
            $message = sprintf('The importer can\'t import: CSV file "%s" does not exist or could not be read.', $file);
            $this->error($message);
            Log::error($message);

            return 1;
        }

        if (!file_exists($config) || (file_exists($config) && !is_file($config))) {
            $message = sprintf('The importer can\'t import: configuration file "%s" does not exist or could not be read.', $config);
            $this->error($message);
            Log::error($message);

            return 1;
        }
        // basic check on the JSON.
        $json = file_get_contents($config);
        try {
            $configuration = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $message = sprintf('The importer can\'t import: could not decode the JSON in the config file: %s' , $e->getMessage());
            $this->error($message);
            Log::error($message);

            return 1;
        }

        $this->line('The import routine is about to start.');
        $this->line('This is invisible and may take quite some time.');
        $this->line('Once finished, you will see a list of errors, warnings and messages (if applicable).');
        $this->line('--------');
        $this->line('Running...');
        $csv    = file_get_contents($file);
        $result = $this->startImport($csv, $configuration);
        if (0 === $result) {
            $this->line('Import complete.');
        }
        if (0 !== $result) {
            $this->warn('The import finished with errors.');
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function haveAccess(): bool
    {
        $request = new SystemInformationRequest();
        try {
            $request->get();
        } catch (ApiHttpException $e) {
            $this->error(sprintf('Could not connect to Firefly III: %s', $e->getMessage()));

            return false;
        }

        return true;
    }

    /**
     * @param string $csv
     * @param array  $configuration
     *
     * @return int
     */
    private function startImport(string $csv, array $configuration): int
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        $configObject = Configuration::fromFile($configuration);
        $manager      = new ImportRoutineManager();

        try {
            $manager->setConfiguration($configObject);
        } catch (ImportException $e) {
            $this->error($e->getMessage());

            return 1;
        }
        $manager->setReader(FileReader::getReaderFromContent($csv));
        try {
            $manager->start();
        } catch (ImportException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $messages = $manager->getAllMessages();
        $warnings = $manager->getAllWarnings();
        $errors   = $manager->getAllErrors();

        if (count($errors) > 0) {
            foreach ($errors as $index => $error) {
                foreach ($error as $line) {
                    $this->error(sprintf('ERROR in line     #%d: %s', $index + 1, $line));
                }
            }
        }

        if (count($warnings) > 0) {
            foreach ($warnings as $index => $warning) {
                foreach ($warning as $line) {
                    $this->warn(sprintf('Warning from line #%d: %s', $index + 1, $line));
                }
            }
        }

        if (count($messages) > 0) {
            foreach ($messages as $index => $message) {
                foreach ($message as $line) {
                    $this->info(sprintf('Message from line #%d: %s', $index + 1, $line));
                }
            }
        }

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\File\FileReader;
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
        $this->info(sprintf('Welcome to the Firefly III CSV importer, v%s', config('csv_importer.version')));
        Log::debug(sprintf('Now in %s',__METHOD__));
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
        $csv = file_get_contents($file);
        $this->startImport($csv, $configuration);

        $this->line('Import complete.');

        return 0;
    }

    /**
     * @param string $csv
     * @param array  $configuration
     */
    private function startImport(string $csv, array $configuration): void
    {
        Log::debug(sprintf('Now in %s',__METHOD__));
        $configObject = Configuration::fromFile($configuration);
        $manager      = new ImportRoutineManager();
        $manager->setConfiguration($configObject);
        $manager->setReader(FileReader::getReaderFromContent($csv));
        $manager->start();

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

    }
}

<?php

namespace App\Console\Commands;

use App\Services\CSV\Configuration\Configuration;
use App\Services\CSV\File\FileReader;
use App\Services\Import\ImportRoutineManager;
use Illuminate\Console\Command;
use JsonException;

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
        $file   = $this->argument('file');
        $config = $this->argument('config');
        if (!file_exists($file) || (file_exists($file) && !is_file($file))) {
            $this->error(sprintf('File "%s" does not exist or could not be read.', $file));

            return 1;
        }

        if (!file_exists($config) || (file_exists($config) && !is_file($config))) {
            $this->error(sprintf('File "%s" does not exist or could not be read.', $config));

            return 1;
        }
        // basic check on the JSON.
        $json = file_get_contents($config);
        try {
            $configuration = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error(sprintf('Could not decode the JSON in the config file: %s' . $e->getMessage()));

            return 1;
        }
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
        $configObject = Configuration::fromFile($configuration);
        $manager      = new ImportRoutineManager();
        $manager->setConfiguration($configObject);
        $manager->setReader(FileReader::getReaderFromContent($csv));
        $manager->start();

        $total    = $manager->getTotal();
        $messages = $manager->getMessages();
        $errors   = $manager->getErrors();

        for ($i = 0; $i < $total; $i++) {
            if (isset($messages[$i])) {
                $this->info(sprintf('#%d: %s', $i, $messages[$i]));
            }
            if (isset($errors[$i])) {
                if (is_array($errors[$i])) {
                    foreach ($errors[$i] as $line) {
                        $this->error(sprintf('#%d: %s', $i, $line));
                    }
                }
                if (!is_array($errors[$i])) {
                    $this->error(sprintf('#%d: %s', $i, $errors[$i]));
                }
            }
        }
    }
}

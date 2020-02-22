<?php

namespace App\Console\Commands;

use App\Console\HaveAccess;
use App\Console\StartImport;
use App\Console\VerifyJSON;
use Illuminate\Console\Command;

/**
 * Class AutoImport
 */
class AutoImport extends Command
{
    use HaveAccess, VerifyJSON, StartImport;

    /** @var array */
    private const IGNORE = ['.', '..'];
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will automatically import from the given directory and use the JSON and CSV files found.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:auto_import {directory : The directory from which to import automatically.}';
    /** @var string */
    private $directory = './';

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
     * @return int
     */
    public function handle(): int
    {
        $access = $this->haveAccess();
        if (false === $access) {
            $this->error('Could not connect to your local Firefly III instance.');

            return 1;
        }

        $this->directory = $this->argument('directory') ?? './';
        $this->line(sprintf('Going to automatically import everything found in %s', $this->directory));
        $files = $this->getFiles();
        if (0 === count($files)) {
            $this->error(sprintf('Found no CSV files in %s', $this->directory));

            return 1;
        }
        $this->line(sprintf('Found %d CSV + JSON file sets in %s', count($files), $this->directory));

        $this->importFiles($files);

        return 0;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getExtension(string $file): string
    {
        $parts = explode('.', $file);
        if (1 === count($parts)) {
            return '';
        }

        return $parts[count($parts) - 1];
    }

    /**
     * @return array
     */
    private function getFiles(): array
    {
        if (null === $this->directory || '' === $this->directory) {
            $this->error(sprintf('Directory "%s" is empty or invalid.', $this->directory));

            return [];
        }
        $files  = array_diff(scandir($this->directory), self::IGNORE);
        $return = [];
        foreach ($files as $file) {
            if ('csv' === $this->getExtension($file) && $this->hasJsonConfiguration($file)) {
                $return[] = $file;
            }
        }

        return $return;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    private function hasJsonConfiguration(string $file): bool
    {
        $short    = substr($file, 0, -4);
        $jsonFile = sprintf('%s.json', $short);
        if (!file_exists(sprintf('%s%s', $this->directory, $jsonFile))) {
            $this->warn(sprintf('Can\'t find JSON file "%s" expected to go with CSV file "%s". CSV file will be ignored.', $jsonFile, $file));

            return false;
        }

        return true;
    }

    /**
     * @param string $file
     */
    private function importFile(string $file): void
    {
        $csvFile  = sprintf('%s%s', $this->directory, $file);
        $jsonFile = sprintf('%s%s.json', $this->directory, substr($file, 0, -4));

        // do JSON check
        $jsonResult = $this->verifyJSON($jsonFile);
        if (false === $jsonResult) {
            $message = sprintf('The importer can\'t import %s: could not decode the JSON in config file %s.', $csvFile, $jsonFile);
            $this->error($message);

            return;
        }
        $configuration = json_decode(file_get_contents($jsonFile), true, 512, JSON_THROW_ON_ERROR);
        $this->line(sprintf('Going to import from file %s using configuration %s.', $csvFile, $jsonFile));
        // create importer
        $csv    = file_get_contents($csvFile);
        $result = $this->startImport($csv, $configuration);
        if (0 === $result) {
            $this->line('Import complete.');
        }
        if (0 !== $result) {
            $this->warn('The import finished with errors.');
        }

        $this->line(sprintf('Done importing from file %s using configuration %s.', $csvFile, $jsonFile));
    }

    /**
     * @param array $files
     */
    private function importFiles(array $files): void
    {
        /** @var string $file */
        foreach ($files as $file) {
            $this->importFile($file);
        }
    }
}

<?php
declare(strict_types=1);
/**
 * AutoImport.php
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

namespace App\Console\Commands;

use App\Console\HaveAccess;
use App\Console\StartImport;
use App\Console\VerifyJSON;
use App\Exceptions\ImportException;
use Illuminate\Console\Command;
use JsonException;
use Log;

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
    protected $signature = 'csv:auto-import {directory : The directory from which to import automatically.}';
    /** @var string */
    private $directory = './';

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

        $this->directory = (string) ($this->argument('directory') ?? './');
        $this->line(sprintf('Going to automatically import everything found in %s', $this->directory));

        $files = $this->getFiles();
        if (0 === count($files)) {
            $this->info(sprintf('There are no files in directory %s', $this->directory));
            $this->info('To learn more about this process, read the docs:');
            $this->info('https://firefly-iii.gitbook.io/firefly-iii-csv-importer/installing-and-running/docker');

            return 1;
        }
        $this->line(sprintf('Found %d CSV + JSON file sets in %s', count($files), $this->directory));
        try {
            $this->importFiles($files);
        } catch (ImportException $e) {
            Log::error($e->getMessage());
            $this->error(sprintf('Import exception (see the logs): %s', $e->getMessage()));
        }

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
        $array = scandir($this->directory);
        if (!is_array($array)) {
            $this->error(sprintf('Directory "%s" is empty or invalid.', $this->directory));

            return [];
        }
        $files  = array_diff($array, self::IGNORE);
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
        $fullJson = sprintf('%s/%s', $this->directory, $jsonFile);
        if (!file_exists($fullJson)) {
            $this->warn(sprintf('Can\'t find JSON file "%s" expected to go with CSV file "%s". CSV file will be ignored.', $fullJson, $file));

            return false;
        }

        return true;
    }

    /**
     * @param string $file
     *
     * @throws ImportException
     */
    private function importFile(string $file): void
    {
        $csvFile  = sprintf('%s/%s', $this->directory, $file);
        $jsonFile = sprintf('%s/%s.json', $this->directory, substr($file, 0, -4));

        // do JSON check
        $jsonResult = $this->verifyJSON($jsonFile);
        if (false === $jsonResult) {
            $message = sprintf('The importer can\'t import %s: could not decode the JSON in config file %s.', $csvFile, $jsonFile);
            $this->error($message);

            return;
        }
        try {
            $configuration = json_decode(file_get_contents($jsonFile), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error($e->getMessage());
            throw new ImportException(sprintf('Bad JSON in configuration file: %s', $e->getMessage()));
        }
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
     *
     * @throws ImportException
     */
    private function importFiles(array $files): void
    {
        /** @var string $file */
        foreach ($files as $file) {
            $this->importFile($file);
        }
    }
}

<?php
/**
 * ListTests.php
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

use Illuminate\Console\Command;

class ListTests extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test command to list stuff.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:list';

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
        $dir = '/sites/FF3/import-tests';
        if (!file_exists($dir)) {
            $this->error('No directory.');

            return 1;
        }
        $command = 'php artisan csv:import %s/%s %s/%s';
        $result  = scandir($dir);
        foreach ($result as $file) {
            $ext = substr($file, -3);
            if ('csv' === $ext) {
                $json    = str_replace('.csv', '.json', $file);
                $current = sprintf($command, $dir, $file, $dir, $json);
                $this->line($current);
            }
        }

        return 0;
    }
}

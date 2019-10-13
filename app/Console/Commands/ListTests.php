<?php

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

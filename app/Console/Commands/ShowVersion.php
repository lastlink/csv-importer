<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowVersion extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Echoes the current version and some debug info.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:version';

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
        $this->line(sprintf('Firefly III CSV importer v%s', config('csv_importer.version')));
        $this->line(sprintf('PHP: %s %s %s', PHP_SAPI, PHP_VERSION, PHP_OS));

        return 0;
    }
}

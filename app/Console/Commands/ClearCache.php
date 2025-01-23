<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to clear the cache.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cache::flush();
        $this->info('Cache cleared successfully');
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Importer\ImporterFacade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetMovies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:get-movies {--trace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get movies';

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
    public function handle()
    {
        $this->info('Starting movies import!');
        $data = Http::get(env('MOVIES_JSON_URL'))->body();
        ImporterFacade::importMovies($data, 30, $this->option('trace'));
        $this->info('Done!');

        return 0;
    }
}

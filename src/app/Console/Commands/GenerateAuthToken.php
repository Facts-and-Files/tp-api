<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an api client auth token for api usage';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = Str::random(60);

        /* DB::table('api_clients')->whereNotnull('api_token')->delete(); */

        DB::table('api_clients')->insert([
            'api_token' => hash('sha256', $token),
        ]);

        $this->info($token);

        return 0;
    }
}

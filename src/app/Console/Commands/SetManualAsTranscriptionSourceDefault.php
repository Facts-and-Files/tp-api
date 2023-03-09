<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class SetManualAsTranscriptionSourceDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:set-transcription-source';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the TranscriptionSource on Item table to »manual« when NULL and change to not nullable';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::statement('UPDATE Item set TranscriptionSource = "manual" where TranscriptionSource is null;');
        DB::statement('ALTER TABLE Item CHANGE TranscriptionSource TranscriptionSource enum("manual", "htr") NOT NULL DEFAULT "manual";');

        return 0;
    }
}

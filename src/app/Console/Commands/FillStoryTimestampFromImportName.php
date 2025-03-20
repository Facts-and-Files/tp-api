<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Carbon\Carbon;

class FillStoryTimestampFromImportName extends Command
{
    protected $signature = 'fill:timestamps';
    protected $description = 'Fill NULL Timestamp columns with datetime extracted from ImportName';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $stories = DB::table('Story')
            ->whereNull('Timestamp')
            ->get();

        foreach ($stories as $story) {
            preg_match('/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})/', $story->ImportName, $matches);

            if (!empty($matches)) {
                $datetime = Carbon::parse($matches[0]);

                DB::table('Story')
                    ->where('StoryId', $story->StoryId)
                    ->update(['Timestamp' => $datetime]);
            }
        }

        $this->info('Timestamps have been filled successfully!');
    }
}

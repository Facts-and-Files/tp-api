<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectDataSeeder extends Seeder
{
    public static $data = [
        [
            'ProjectId' => 1,
            'Name'      => 'Project-1',
            'Url'       => 'project-1'
        ],
        [
            'ProjectId' => 2,
            'Name'      => 'Project-2',
            'Url'       => 'project-2'
        ]
    ];

    public function run(): void
    {
        DB::table('Project')->insert(self::$data);
    }
}

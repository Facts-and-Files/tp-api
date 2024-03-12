<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER table Place MODIFY Latitude DECIMAL(10,6) not null');
        DB::statement('ALTER table Place MODIFY Longitude DECIMAL(10,6) not null');
    }

    public function down(): void
    {
        DB::statement('ALTER table Place MODIFY Latitude FLOAT not null');
        DB::statement('ALTER table Place MODIFY Longitude FLOAT not null');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `ItemProperty` ALTER `UserGenerated` SET DEFAULT b'1'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `ItemProperty` ALTER `UserGenerated` SET DEFAULT b'0'");
    }
};

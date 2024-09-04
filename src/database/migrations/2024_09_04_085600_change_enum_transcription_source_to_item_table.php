<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE Item MODIFY TranscriptionSource ENUM('manual','htr','occam') DEFAULT 'manual'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE Item MODIFY TranscriptionSource ENUM('manual','htr') DEFAULT 'manual'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE PropertyType MODIFY MotivationId SMALLINT(6) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE PropertyType MODIFY MotivationId SMALLINT(6) NOT NULL");
    }
};

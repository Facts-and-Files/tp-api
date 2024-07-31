<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Story', function (Blueprint $table) {
            $table
                ->string('Manifest', 1000)
                ->after('HasHtr')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('Story', function (Blueprint $table) {
            $table->dropColumn('Manifest');
        });
    }
};

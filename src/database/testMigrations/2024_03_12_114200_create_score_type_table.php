<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ScoreType', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->smallIncrements('ScoreTypeId');
            $table->string('Name', 100);
            $table->float('Rate');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ScoreType');
    }
};


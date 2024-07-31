<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CompletionStatus', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->smallIncrements('CompletionStatusId');
            $table->string('Name',              45)->nullable();
            $table->string('ColorCode',         45)->nullable();
            $table->string('ColorCodeGradient', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CompletionStatus');
    }
};


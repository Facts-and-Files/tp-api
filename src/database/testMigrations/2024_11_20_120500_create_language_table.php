<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Language', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->smallIncrements('LanguageId');
            $table->string('Name', 100);
            $table->string('NameEnglish', 100);
            $table->string('ShortName', 10);
            $table->string('Code', 10);
            $table->string('Code3', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Language');
    }
};


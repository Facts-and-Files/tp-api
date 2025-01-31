<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TranscriptionLanguage', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('TranscriptionLanguageId');
            $table->bigInteger('TranscriptionId');
            $table->smallInteger('LanguageId');

            $table->foreign('TranscriptionId')
                  ->references('TranscriptionId')
                  ->on('Transcription')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('LanguageId')
                  ->references('LanguageId')
                  ->on('Language')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TranscriptionLanguage');
    }
};


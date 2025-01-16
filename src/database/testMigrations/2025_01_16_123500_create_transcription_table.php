<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Transcription', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('TranscriptionId');
            $table->mediumText('Text');
            $table->mediumText('TextNoTags');
            $table->integer('UserId');
            $table->integer('ItemId');
            $table->boolean('NoText')->default(0);
            $table->boolean('CurrentVersion');
            $table->bigInteger('EuropeanaAnnotationId')->nullable();
            $table->dateTime('Timestamp')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Transcription');
    }
};

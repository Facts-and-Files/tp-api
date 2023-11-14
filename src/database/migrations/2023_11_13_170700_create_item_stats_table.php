<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ItemStats', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('ItemId')->unique();
            $table->integer('StoryId');
            $table->dateTime('EditStart')->nullable();
            $table->json('UserIds');
            $table->integer('TranscribedCharsManual')->default(0);
            $table->integer('TranscribedCharsHtr')->default(0);
            $table->json('Enrichments');
            $table->dateTime('Timestamp');
            $table->dateTime('LastUpdated');

            $table->index('ItemId');
            $table->index('StoryId');

            $table->foreign('ItemId')
                  ->references('ItemId')
                  ->on('Item')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('StoryId')
                  ->references('StoryId')
                  ->on('Story')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ItemStats');
    }
};


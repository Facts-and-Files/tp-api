<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('AutoEnrichment', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('AutoEnrichmentId');
            $table->string('Name', 255);
            $table->string('Type', 64);
            $table->string('WikiData', 255);
            $table->integer('ItemId')->nullable();
            $table->integer('StoryId')->nullable();
            $table->string('ExternalAnnotationId', 256);
            $table->dateTime('Timestamp');
            $table->dateTime('LastUpdated');

            $table->index('Name');
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('AutoEnrichment');
    }
};

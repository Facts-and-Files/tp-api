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
        Schema::create('AutoEnrichments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('AutoEnrichmentId');
            $table->string('Name', 64);
            $table->string('Type', 64);
            $table->string('WikiData', 64);
            $table->integer('ItemId')->nullable();
            $table->integer('StoryId')->nullable();
            $table->string('ExternalId', 256);
            $table->dateTime('Timestamp');
            $table->dateTime('LastUpdated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('AutoEnrichments');
    }
};

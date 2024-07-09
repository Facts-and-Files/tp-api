<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Place', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('PlaceId');
            $table->string('Name', 255)->nullable();
            $table->decimal('Latitude', 10, 6);
            $table->decimal('Longitude', 10, 6);
            $table->integer('ItemId');
            $table->string('Link', 1000)->nullable();
            $table->smallInteger('Zoom')->default(10)->nullable();
            $table->string('Comment', 1000)->nullable();
            $table->boolean('UserGenerated')->default(false);
            $table->integer('UserId')->nullable();
            $table->string('WikidataName', 255)->nullable();
            $table->string('WikidataId', 45)->nullable();
            $table->enum('PlaceRole', ['CreationPlace', 'Other'])->default('Other');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Person');
    }
};


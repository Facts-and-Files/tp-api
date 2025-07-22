<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Story', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('StoryId');
            $table->text('dc:title');
            $table->longText('dc:description')->nullable();
            $table->text('dc:creator')->nullable();
            $table->text('dc:source')->nullable();
            $table->text('dc:contributor')->nullable();
            $table->text('dc:publisher')->nullable();
            $table->text('dc:coverage')->nullable();
            $table->text('dc:date')->nullable();
            $table->text('dc:type')->nullable();
            $table->text('dc:relation')->nullable();
            $table->text('dc:rights')->nullable();
            $table->string('dc:language', 255)->nullable();
            $table->string('dc:identifier', 255)->nullable();
            $table->text('edm:landingPage')->nullable();
            $table->text('edm:country')->nullable();
            $table->text('edm:dataProvider')->nullable();
            $table->text('edm:provider')->nullable();
            $table->text('edm:rights')->nullable();
            $table->text('edm:year')->nullable();
            $table->text('edm:datasetName')->nullable();
            $table->text('edm:begin')->nullable();
            $table->text('edm:end')->nullable();
            $table->text('edm:isShownAt')->nullable();
            $table->string('edm:language', 255)->nullable();
            $table->text('edm:agent')->nullable();
            $table->text('dcterms:medium')->nullable();
            $table->string('dcterms:provenance', 255)->nullable();
            $table->text('dcterms:created')->nullable();
            $table->text('ExternalRecordId')->nullable();
            $table->string('PlaceName', 255)->nullable();
            $table->float('PlaceLatitude')->nullable();
            $table->float('PlaceLongitude')->nullable();
            $table->smallInteger('placeZoom')->nullable()->default(10);
            $table->string('PlaceLink', 1000)->nullable();
            $table->string('PlaceComment', 1000)->nullable();
            $table->integer('PlaceUserId')->nullable();
            // $table->integer('OldStoryId')->nullable();
            // $table->text('Summary')->nullable();
            // $table->integer('ParentStory')->nullable();
            // $table->longText('SearchText');
            // $table->integer('OrderIndex')->nullable();
            $table->boolean('PlaceUserGenerated')->nullable();
            $table->dateTime('DateStart')->nullable();
            $table->dateTime('DateEnd')->nullable();
            $table->tinyInteger('Public')->default(0);
            $table->string('ImportName', 1000)->nullable();
            $table->smallInteger('ProjectId');
            $table->smallInteger('CompletionStatusId')->default(1);
            $table->string('RecordId', 255)->nullable();
            $table->text('PreviewImage')->nullable();
            $table->smallInteger('DatasetId')->nullable();
            $table->string('StoryLanguage', 255)->nullable();
            $table->tinyInteger('HasHtr')->default(0);
            $table->dateTime('LastUpdated')->useCurrent();
            $table->dateTime('Timestamp')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Story');
    }
};


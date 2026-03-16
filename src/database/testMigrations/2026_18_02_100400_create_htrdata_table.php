<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('HtrData', function (Blueprint $table) {
            $table->unsignedBigInteger('HtrDataId')->autoIncrement();

            $table->integer('ItemId');
            $table->string('HtrProcessId')->nullable();
            $table->integer('HtrModelId')->nullable();
            $table->string('HtrStatus', 64)->nullable();

            $table->unsignedBigInteger('EuropeanaAnnotationId')->nullable();
            $table->unsignedSmallInteger('TranscriptionProviderId')->nullable();

            $table->dateTime('Timestamp');
            $table->dateTime('LastUpdated');

            $table->index('ItemId');
            $table->index('HtrProcessId');
            $table->index('HtrStatus');
            $table->index('TranscriptionProviderId');

            $table->foreign('ItemId')
                ->references('ItemId')
                ->on('Item')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('TranscriptionProviderId')
                ->references('TranscriptionProviderId')
                ->on('TranscriptionProvider')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('HtrData', function (Blueprint $table) {
            $table->dropForeign(['ItemId']);
            $table->dropForeign(['TranscriptionProviderId']);
        });

        Schema::dropIfExists('HtrData');
    }
};

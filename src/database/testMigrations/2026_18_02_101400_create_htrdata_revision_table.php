<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('HtrDataRevision', function (Blueprint $table) {
            $table->unsignedBigInteger('HtrDataRevisionId')->autoIncrement();

            $table->unsignedBigInteger('HtrDataId');
            $table->integer('UserId')->nullable();

            $table->mediumText('TranscriptionData')->nullable();
            $table->mediumText('TranscriptionText')->nullable();

            $table->dateTime('Timestamp');
            $table->dateTime('LastUpdated');

            $table->index('HtrDataId');
            $table->index('UserId');

            $table->foreign('HtrDataId')
                ->references('HtrDataId')
                ->on('HtrData')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('UserId')
                ->references('UserId')
                ->on('User')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('HtrDataRevision', function (Blueprint $table) {
            $table->dropForeign(['HtrDataId']);
            $table->dropForeign(['UserId']);
        });

        Schema::dropIfExists('HtrDataRevision');
    }
};

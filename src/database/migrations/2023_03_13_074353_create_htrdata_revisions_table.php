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
        Schema::create('HtrDataRevision', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('HtrDataRevisionId');
            $table->unsignedBigInteger('HtrDataId');
            $table->integer('UserId')->nullable();
            $table->mediumtext('TranscriptionData')->nullable();
            $table->mediumText('TranscriptionText')->nullable();
            $table->dateTime('Timestamp');
            $table->dateTime('LastUpdated');

            $table->index('HtrDataId');
            $table->index('UserId');

            $table->foreign('HtrDataId')
                  ->references('HtrDataId')
                  ->on('HtrData')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreign('UserId')
                  ->references('UserId')
                  ->on('User')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('HtrDataRevision');
    }
};

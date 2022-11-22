<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHtrDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('HtrData', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('HtrDataId');
            $table->integer('ItemId');
            $table->integer('UserId')->nullable();
            $table->integer('ProcessId')->unique()->nullable();
            $table->integer('HtrId')->nullable();
            $table->string('HtrStatus', 64)->nullable();
            $table->mediumtext('TranscriptionData')->nullable();
            $table->dateTime('Timestamp');
            $table->dateTime('LastUpdated');

            $table->index('ItemId');
            $table->index('UserId');
            $table->index('ProcessId');

            $table->foreign('ItemId')
                  ->references('ItemId')
                  ->on('Item')
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
        Schema::dropIfExists('HtrData');
    }
}

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
        Schema::create('htr_data', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->integer('item_id');
            $table->integer('user_id')->nullable();
            $table->integer('process_id')->unique()->nullable();
            $table->integer('htr_id')->nullable();
            $table->string('htr_status', 64)->nullable();
            $table->mediumtext('data')->nullable();
            $table->bigInteger('europeana_annotation_id')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('user_id');
            $table->index('process_id');

            $table->foreign('item_id')
                  ->references('ItemId')
                  ->on('Item')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreign('user_id')
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
        Schema::dropIfExists('htr_data');
    }
}

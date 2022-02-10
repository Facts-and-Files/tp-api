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
            $table->integer('item_id')->unique()->nullable(false);
            $table->integer('process_id')->unique()->nullable(false);
            $table->integer('htr_id')->nullable();
            $table->string('status', 64)->nullable();
            $table->mediumtext('data')->nullable();
            $table->string('data_type', 16)->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('ItemId')->on('Item');
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

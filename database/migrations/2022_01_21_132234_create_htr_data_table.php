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

            $table->integer('ItemId')->primary()->nullable(false);
            $table->integer('ProcessId')->unique()->nullable(false);
            $table->integer('HtrId')->nullable();
            $table->string('Status', 64)->nullable();
            $table->dateTime('LastUpdated')->useCurrent()->nullable(false);
            $table->mediumtext('Data')->nullable();
            $table->string('DataType', 16)->nullable();

            $table->foreign('ItemId')->references('ItemId')->on('Item');
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

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
        Schema::table('Item', function (Blueprint $table) {
            $table
                ->enum('DateRole', ['CreationDate', 'Other'])
                ->after('DateEndDisplay')
                ->default('Other');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Item', function (Blueprint $table) {
            $table->dropColumn('DateRole');
        });
    }
};

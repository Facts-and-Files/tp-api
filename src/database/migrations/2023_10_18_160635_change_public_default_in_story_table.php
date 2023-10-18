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
        Schema::table('Story', function (Blueprint $table) {
            $table
                ->boolean('Public_boolean')
                ->after('Public')
                ->default(0);
        });

        DB::table('Story')->update(['Public_boolean' => DB::raw('Public')]);

        Schema::table('Story', function (Blueprint $table) {
            $table->dropColumn('Public');
        });

        DB::statement('ALTER TABLE `Story` CHANGE `Public_boolean` `Public` tinyint(1) NOT NULL DEFAULT 0 AFTER `OrderIndex`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Story', function (Blueprint $table) {
            $table
                ->boolean('Public_boolean')
                ->after('Public')
                ->default(1);
        });

        DB::table('Story')->update(['Public_boolean' => DB::raw('Public')]);

        Schema::table('Story', function (Blueprint $table) {
            $table->dropColumn('Public');
        });

        DB::statement('ALTER TABLE `Story` CHANGE `Public_boolean` `Public` tinyint(1) NOT NULL DEFAULT 1 AFTER `OrderIndex`');
    }
};

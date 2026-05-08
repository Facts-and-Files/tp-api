<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('CacheExport', function (Blueprint $table) {
            $table->string('Format_tmp', 32)->nullable()->after('Format');
        });

        DB::statement('UPDATE `CacheExport` SET `Format_tmp` = `Format`');

        Schema::table('CacheExport', function (Blueprint $table) {
            $table->dropUnique(['ItemId', 'Format']);
            $table->dropColumn('Format');
        });

        Schema::table('CacheExport', function (Blueprint $table) {
            $table->string('Format', 32)->default('alto')->after('ItemId');
        });

        DB::statement('UPDATE `CacheExport` SET `Format` = `Format_tmp`');

        Schema::table('CacheExport', function (Blueprint $table) {
            $table->dropColumn('Format_tmp');
            $table->unique(['ItemId', 'Format']);
        });
    }

    public function down(): void
    {
        Schema::table('CacheExport', function (Blueprint $table) {
            $table->enum('Format_tmp', ['alto', 'tei', 'yml', 'csv'])
                ->nullable()
                ->after('Format');
        });

        DB::statement('UPDATE `CacheExport` SET `Format_tmp` = `Format` WHERE `Format` IN ("alto", "tei", "yml", "csv")');

        Schema::table('CacheExport', function (Blueprint $table) {
            $table->dropUnique(['ItemId', 'Format']);
            $table->dropColumn('Format');
        });

        Schema::table('CacheExport', function (Blueprint $table) {
            $table->enum('Format', ['alto', 'tei', 'yml', 'csv'])
                ->default('alto')
                ->after('ItemId');
        });

        DB::statement('UPDATE `CacheExport` SET `Format` = `Format_tmp`');

        Schema::table('CacheExport', function (Blueprint $table) {
            $table->dropColumn('Format_tmp');
            $table->unique(['ItemId', 'Format']);
        });
    }
};

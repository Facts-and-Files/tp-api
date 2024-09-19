<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('TranscriptionProvider', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->smallIncrements('TranscriptionProviderId');
            $table->string('Name');
        });

        DB::table('TranscriptionProvider')->insert([
            ['Name' => 'ReadCoop-Transkribus'],
            ['Name' => 'CrossLang-Occam']
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('TranscriptionProvider');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Story', function (Blueprint $table) {
            $table
                ->enum('TranscriptionSource', ['manual', 'htr'])
                ->default('manual')
                ->after('StoryLanguage');
        });
    }

    public function down()
    {
        Schema::table('Story', function (Blueprint $table) {
            $table->dropColumn('TranscriptionSource');
        });
    }
};

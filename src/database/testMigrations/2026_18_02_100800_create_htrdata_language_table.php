<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('HtrDataLanguage', function (Blueprint $table) {
            $table->unsignedBigInteger('HtrDataId');
            $table->smallInteger('LanguageId');

            $table->primary(['HtrDataId', 'LanguageId']);

            $table->index('HtrDataId');
            $table->index('LanguageId');

            $table->foreign('HtrDataId')
                ->references('HtrDataId')
                ->on('HtrData')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('LanguageId')
                ->references('LanguageId')
                ->on('Language')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {

        Schema::table('HtrDataLanguage', function (Blueprint $table) {
            $table->dropForeign(['HtrDataId']);
            $table->dropForeign(['LanguageId']);
        });

        Schema::dropIfExists('HtrDataLanguage');
    }
};

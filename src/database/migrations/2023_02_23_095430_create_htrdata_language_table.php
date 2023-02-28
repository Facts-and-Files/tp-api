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
        Schema::create('HtrDataLanguage', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('HtrDataId');
            $table->smallInteger('LanguageId');

            $table->primary(['HtrDataId', 'LanguageId']);
            $table->index('HtrDataId');
            $table->index('LanguageId');

            $table->foreign('HtrDataId')
                  ->references('HtrDataId')
                  ->on('HtrData')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreign('LanguageId')
                  ->references('LanguageId')
                  ->on('Language')
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
        Schema::dropIfExists('HtrDataLanguage');
    }
};

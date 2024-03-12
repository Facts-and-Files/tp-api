<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Score', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('ScoreId');
            $table->integer('ItemId');
            $table->integer('UserId');
            $table->smallInteger('ScoreTypeId');
            $table->integer('Amount');
            $table->dateTime('Timestamp');

            $table->index('ItemId');
            $table->index('UserId');
            $table->index('ScoreTypeId');

            $table->foreign('ItemId')
                  ->references('ItemId')
                  ->on('Item')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('UserId')
                  ->references('UserId')
                  ->on('User')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('ScoreTypeId')
                  ->references('ScoreTypeId')
                  ->on('ScoreType')
                  ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Score');
    }
};


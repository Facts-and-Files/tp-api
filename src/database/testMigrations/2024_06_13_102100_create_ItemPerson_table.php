<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ItemPerson', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('ItemPersonId');
            $table->integer('ItemId');
            $table->integer('PersonId');

            $table->foreign('PersonId')
                  ->references('PersonId')
                  ->on('Person')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreign('ItemId')
                  ->references('ItemId')
                  ->on('Item')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ItemPerson');
    }
};


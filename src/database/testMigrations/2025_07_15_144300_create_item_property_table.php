<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ItemProperty', function (Blueprint $table) {
            $table->bigIncrements('ItemPropertyId');
            $table->integer('ItemId');
            $table->bigInteger('PropertyId');
            $table->boolean('UserGenerated')->default(false);

            $table->unique(['ItemId','PropertyId']);

            $table->foreign('ItemId')
                  ->references('ItemId')
                  ->on('Item')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('PropertyId')
                  ->references('PropertyId')
                  ->on('Property')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ItemProperty');
    }
};

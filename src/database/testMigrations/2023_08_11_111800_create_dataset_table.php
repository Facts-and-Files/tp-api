<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Dataset', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->tinyIncrements('DatasetId');
            $table->string('Name', 100);
            $table->tinyInteger('ProjectId');

            $table->foreign('ProjectId')
                  ->references('ProjectId')
                  ->on('Project')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('Dataset');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Item', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('ItemId');
            $table->smallInteger('CompletionStatusId')->default(1);
            $table->smallInteger('TranscriptionStatusId')->default(1);
            $table->smallInteger('TaggingStatusId')->default(1);
            $table->smallInteger('LocationStatusId')->default(1);
            $table->dateTime('LastUpdated')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Item');
    }
};


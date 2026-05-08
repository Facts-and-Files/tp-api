<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('CacheExport', function (Blueprint $table) {
            $table->id();
            $table->integer('ItemId')->index();
            $table->enum('Format', ['alto', 'tei', 'yml', 'csv'])->default('alto');
            $table->dateTime('GeneratedAt');
            $table->dateTime('SourceUpdatedAt');
            $table->text('FilePath');

            $table->unique(['ItemId', 'Format']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CacheExport');
    }
};

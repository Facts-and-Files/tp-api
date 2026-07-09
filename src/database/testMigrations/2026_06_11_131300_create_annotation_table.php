<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Annotation', function (Blueprint $table) {
            $table->integer('AnnotationId')->primary();
            $table->text('Text')->nullable();
            $table->text('TextNoTags')->nullable();
            $table->integer('ItemId');
            $table->integer('AnnotationTypeId')->nullable();
            $table->integer('EuropeanaAnnotationId')->nullable();
            $table->float('X_Coord')->default(0);
            $table->float('Y_Coord')->default(0);
            $table->float('Width')->default(0);
            $table->float('Height')->default(0);
            $table->timestamp('Timestamp')->nullable();

            $table->foreign('ItemId')
                ->references('ItemId')
                ->on('Item')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Annotation');
    }
};

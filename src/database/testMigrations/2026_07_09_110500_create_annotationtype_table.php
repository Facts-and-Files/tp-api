<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('AnnotationType', function (Blueprint $table) {
            $table->integer('AnnotationTypeId')->primary();
            $table->string('Name', 100);
            $table->integer('MotivationId');

            $table->foreign('MotivationId')
                ->references('MotivationId')
                ->on('Motivation')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AnnotationType');
    }
};

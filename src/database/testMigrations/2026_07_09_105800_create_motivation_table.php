<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('Motivation', function (Blueprint $table) {
            $table->integer('MotivationId')->primary();
            $table->string('Name', 100);
            $table->integer('ProjectId');

            $table->foreign('ProjectId')
                ->references('ProjectId')
                ->on('Project')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Motivation');
    }
};

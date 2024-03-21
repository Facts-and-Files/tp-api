<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Project', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->smallIncrements('ProjectId');
            $table->string('Name', 100);
            $table->string('Url', 45);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('Project');
    }
};


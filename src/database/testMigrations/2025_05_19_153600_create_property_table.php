<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Property', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('PropertyId');
            $table->string('Value', 750);
            $table->string('Description', 1000);
            $table->smallInteger('PropertyTypeId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Property');
    }
};


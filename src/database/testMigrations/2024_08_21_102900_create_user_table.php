<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('User', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('UserId');
            $table->integer('WP_UserId');
            $table->dateTime('Timestamp')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('User');
    }
};


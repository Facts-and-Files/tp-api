<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_clients', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('api_token', 255);
        });

        DB::table('api_clients')->insert([
            'id' => 1,
            'api_token' => 'a8595a37682ac4892995c7d6396d0f82e332625db02c0be7c22fe99e265a1bdf'
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};


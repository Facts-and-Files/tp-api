<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('api_clients', function (Blueprint $table) {
            $table->string('description')->nullable()->after('api_token');
            $table->string('permissions')->default('["*"]')->after('api_token');
        });

    }

    public function down(): void
    {
        Schema::table('api_clients', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('permissions');
        });
    }
};

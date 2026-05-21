<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('Team', function (Blueprint $table) {
            $table->increments('TeamId');
            $table->string('Name', 255);
            $table->string('ShortName', 10);
            $table->string('Code', 255)->nullable();
            $table->string('Description', 255)->nullable();
            $table->boolean('EventUser')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Team');
    }
};

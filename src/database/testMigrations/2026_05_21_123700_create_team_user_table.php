<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('TeamUser', function (Blueprint $table) {
            $table->unsignedInteger('TeamId');
            $table->unsignedInteger('UserId');

            $table->primary(['TeamId', 'UserId']);

            $table->foreign('TeamId', 'FK_46')
                ->references('TeamId')
                ->on('Team')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('UserId', 'FK_50')
                ->references('UserId')
                ->on('User')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TeamUser');
    }
};

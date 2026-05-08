<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('PlaceLink', function (Blueprint $table) {
            $table->bigIncrements('PlaceLinkId');
            $table->bigInteger('PlaceId');
            $table->string('Provider', 255);
            $table->string('Url', 1000);
            $table->timestamps();

            $table->foreign('PlaceId')
                ->references('PlaceId')
                ->on('Place')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index('PlaceId');
            $table->index('Provider');
        });
    }

    public function down(): void
    {
        Schema::table('PlaceLink', function (Blueprint $table) {
            $table->dropForeign(['PlaceId']);
        });

        Schema::dropIfExists('PlaceLink');
    }
};

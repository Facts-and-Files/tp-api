<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Person', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('PersonId');
            $table->string('FirstName', 255)->nullable();
            $table->string('LastName', 255)->nullable();
            $table->string('BirthPlace', 255)->nullable();
            $table->date('BirthDate')->nullable();
            $table->string('BirthDateDisplay', 255)->nullable();
            $table->string('DeathPlace', 255)->nullable();
            $table->date('DeathDate')->nullable();
            $table->string('DeathDateDisplay', 255)->nullable();
            $table->string('Link', 1000)->nullable();
            $table->string('Description', 1000)->nullable();
            $table->enum('PersonRole', ['DocumentCreator', 'AddressedPerson', 'PersonMentioned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Person');
    }
};


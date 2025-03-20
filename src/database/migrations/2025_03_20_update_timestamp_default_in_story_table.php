<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE `Story` MODIFY `Timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        DB::statement('ALTER TABLE `Story` MODIFY `Timestamp` DATETIME NULL');
    }
};

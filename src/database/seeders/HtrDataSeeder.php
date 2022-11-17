<?php

namespace Database\Seeders;

use App\Models\HtrData;
use Illuminate\Database\Seeder;

class HtrDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $htrData = new HtrData();
        $htrData::truncate();

        for ($i = 0; $i <= 9; $i++) {
            $htrData = new HtrData();
            $htrData->item_id = 39348894 - $i;
            $htrData->process_id = random_int(10000, 99999);
            $htrData->htr_id = 11111;
            $htrData->htr_status = 'CREATED';
            $htrData->transcription_data = '<xml />';
            $htrData->europeana_annotation_id = null;
            $htrData->save();
        }
    }
}

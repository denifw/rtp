<?php

use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('office')->insert(['of_rel_id' => 1, 'of_main' => 'Y', 'of_name' => 'SMI', 'of_invoice' => 'Y', 'of_address' => 'Jalan Kramat Jaya No. 48, Tugu Utara', 'of_cnt_id' => 104, 'of_stt_id' => 11, 'of_cty_id' => 171, 'of_dtc_id' => 1, 'of_postal_code' => '', 'of_longitude' => null, 'of_latitude' => null, 'of_active' => 'Y', 'of_created_on' => date('Y-m-d H:i:s'), 'of_created_by' => 1]);
    }
}

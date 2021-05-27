<?php

use Illuminate\Database\Seeder;

class OfficeSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('office')->insert(['of_rel_id' => 3, 'of_main' => 'Y', 'of_name' => 'BMA', 'of_invoice' => 'Y', 'of_address' => 'Jl. Cideng Barat No.14-17, RT.11/RW.1, Duri Pulo', 'of_cnt_id' => 104, 'of_stt_id' => 6, 'of_cty_id' => 58, 'of_dtc_id' => 725, 'of_postal_code' => '10140', 'of_longitude' => 106.8080929, 'of_latitude' => -6.1634196, 'of_active' => 'Y', 'of_created_on' => date('Y-m-d H:i:s'), 'of_created_by' => 1]);
        DB::table('office')->insert(['of_rel_id' => 4, 'of_main' => 'Y', 'of_name' => 'ISA', 'of_invoice' => 'Y', 'of_address' => 'Blok BG/, Jl. Griya Utama No.17, RT.2/RW.5, Sunter Agung', 'of_cnt_id' => 104, 'of_stt_id' => 6, 'of_cty_id' => 61, 'of_dtc_id' => 760, 'of_postal_code' => '14350', 'of_longitude' => 106.8519863, 'of_latitude' => -6.1418602, 'of_active' => 'Y', 'of_created_on' => date('Y-m-d H:i:s'), 'of_created_by' => 1]);
        DB::table('office')->insert(['of_rel_id' => 2, 'of_main' => 'N', 'of_name' => 'Gudang Dadap', 'of_invoice' => 'N', 'of_address' => 'Jl. Raya Perancis II, Dadap Kosambi', 'of_cnt_id' => 104, 'of_stt_id' => 3, 'of_cty_id' => 36, 'of_dtc_id' => 455, 'of_postal_code' => '15213', 'of_longitude' => null, 'of_latitude' => null, 'of_active' => 'Y', 'of_created_on' => date('Y-m-d H:i:s'), 'of_created_by' => 1]);
    }
}

<?php

use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('system_setting')->insert(['ss_relation' => 'PT Spada Media Informatika', 'ss_lg_id' => 1, 'ss_cur_id' => 1, 'ss_decimal_number' => 2, 'ss_decimal_separator' => ',', 'ss_thousand_separator' => '.', 'ss_logo' => 'spada1556082418.png', 'ss_name_space' => 'spada', 'ss_system' => 'Y', 'ss_active' => 'Y', 'ss_created_on' => date('Y-m-d H:i:s'), 'ss_created_by' => 1]);
    }
}

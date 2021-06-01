<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('system_setting')->insert([
            'ss_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'ss1'),
            'ss_relation' => 'System Administrator',
            'ss_lg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'lg2'),
            'ss_cur_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'cur1'),
            'ss_decimal_number' => 2,
            'ss_decimal_separator' => ',',
            'ss_thousand_separator' => '.',
            'ss_logo' => 'spada1556082418.png',
            'ss_name_space' => 'system',
            'ss_rel_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'rel1'),
            'ss_system' => 'Y',
            'ss_active' => 'Y',
            'ss_created_on' => date('Y-m-d H:i:s'),
            'ss_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
    }
}

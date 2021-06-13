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
        DB::table('system_setting')->insert(['ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'ss_relation' => 'System Administrator', 'ss_decimal_number' => 2, 'ss_decimal_separator' => ',', 'ss_thousand_separator' => '.', 'ss_lg_id' => 'c5b453c7-a319-36a2-b854-1789c930733d', 'ss_cur_id' => 'aea8105b-e7e1-3b07-a419-53f356f8eac8', 'ss_logo_id' => '4facc460-0695-3d8f-b2d1-3d68a6c612c7', 'ss_name_space' => 'system', 'ss_system' => 'Y', 'ss_active' => 'Y', 'ss_icon_id' => 'b2101392-f23c-3658-9e3b-3ee09cd2e89f', 'ss_rel_id' => 'f545a902-b73b-3658-a752-db5ece6cdb08', 'ss_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ss_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_setting')->insert(['ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'ss_relation' => 'PT Nusantara Construction', 'ss_decimal_number' => 2, 'ss_decimal_separator' => ',', 'ss_thousand_separator' => '.', 'ss_lg_id' => 'c5b453c7-a319-36a2-b854-1789c930733d', 'ss_cur_id' => 'aea8105b-e7e1-3b07-a419-53f356f8eac8', 'ss_logo_id' => '5cf4f24b-d885-3464-bb00-185ffb8c4480', 'ss_name_space' => 'nc', 'ss_system' => 'N', 'ss_active' => 'Y', 'ss_icon_id' => '7c931e7b-8351-3d6d-bed3-11c68c964558', 'ss_rel_id' => '07fb49e6-8d87-332e-a3e2-9fec83b597d4', 'ss_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ss_created_on' => date('Y-m-d H:i:s')]);
    }
}

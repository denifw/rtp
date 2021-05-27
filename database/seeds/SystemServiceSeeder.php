<?php

use Illuminate\Database\Seeder;

class SystemServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('system_service')->insert(['ssr_ss_id' => 2, 'ssr_srv_id' => 3, 'ssr_srt_id' => 16, 'ssr_active' => 'Y', 'ssr_uid' => '9298c9bf-3443-37cb-b779-62b0d6c44871', 'ssr_created_on' => date('Y-m-d H:i:s'), 'ssr_created_by' => 1]);
        DB::table('system_service')->insert(['ssr_ss_id' => 2, 'ssr_srv_id' => 3, 'ssr_srt_id' => 17, 'ssr_active' => 'Y', 'ssr_uid' => '5d353462-d904-3025-80dd-8f5fa51fcd0d', 'ssr_created_on' => date('Y-m-d H:i:s'), 'ssr_created_by' => 1]);
        DB::table('system_service')->insert(['ssr_ss_id' => 2, 'ssr_srv_id' => 3, 'ssr_srt_id' => 19, 'ssr_active' => 'Y', 'ssr_uid' => '7b5fc4af-2b89-3fc2-bea5-7539671d287d', 'ssr_created_on' => date('Y-m-d H:i:s'), 'ssr_created_by' => 1]);
        DB::table('system_service')->insert(['ssr_ss_id' => 2, 'ssr_srv_id' => 3, 'ssr_srt_id' => 15, 'ssr_active' => 'Y', 'ssr_uid' => '455134d3-5511-3801-b9e8-275ec8d3e67c', 'ssr_created_on' => date('Y-m-d H:i:s'), 'ssr_created_by' => 1]);
        DB::table('system_service')->insert(['ssr_ss_id' => 2, 'ssr_srv_id' => 3, 'ssr_srt_id' => 18, 'ssr_active' => 'Y', 'ssr_uid' => '6954a895-af45-3c50-9350-c43e435c63ea', 'ssr_created_on' => date('Y-m-d H:i:s'), 'ssr_created_by' => 1]);
    }
}

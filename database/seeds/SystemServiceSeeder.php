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
        DB::table('system_service')->insert(['ssr_id' => '089358d8-a802-3b1e-be40-deb83df0b5ac', 'ssr_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'ssr_srv_id' => '020a60ee-d45d-34ae-9c9f-765319e9d6a7', 'ssr_active' => 'Y', 'ssr_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ssr_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_service')->insert(['ssr_id' => '6d590175-22c3-30cb-a06c-fd2aab6f6e62', 'ssr_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'ssr_srv_id' => '373387ad-5421-3500-93e6-318a950d5bfb', 'ssr_active' => 'Y', 'ssr_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ssr_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_service')->insert(['ssr_id' => '944b88d2-63a5-3cec-8619-92cee4884f0c', 'ssr_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'ssr_srv_id' => '373387ad-5421-3500-93e6-318a950d5bfb', 'ssr_active' => 'Y', 'ssr_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ssr_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_service')->insert(['ssr_id' => 'dea9bc4c-b76c-32eb-b71b-ee9ce70103f6', 'ssr_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'ssr_srv_id' => '020a60ee-d45d-34ae-9c9f-765319e9d6a7', 'ssr_active' => 'Y', 'ssr_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ssr_created_on' => date('Y-m-d H:i:s')]);
    }
}

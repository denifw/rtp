<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class UserMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_mapping')->insert(['ump_id' => '2727f669-205b-3778-bd2a-4cae333dca0d', 'ump_us_id' => 'c1d5f0bf-4a0f-39ba-a542-8dd7d55d7602', 'ump_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'ump_rel_id' => '07fb49e6-8d87-332e-a3e2-9fec83b597d4', 'ump_cp_id' => '67d3d4e8-6872-345a-ac9b-2268db54f193', 'ump_confirm' => 'Y', 'ump_default' => 'Y', 'ump_active' => 'Y', 'ump_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ump_created_on' => date('Y-m-d H:i:s')]);
    }
}

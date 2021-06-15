<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SerialNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('serial_number')->insert(['sn_id' => 'b5dd5dc9-74fb-3667-b899-412dd7058105', 'sn_sc_id' => 'd5f39088-b140-3216-b63c-4c3ca180d5f9', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'CP', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'e22c78b6-9127-33ff-9876-9bcf649a1756', 'sn_sc_id' => 'f3815363-ea4b-3748-9710-857efb4b222d', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'REL', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'c0bb944a-7565-31b4-a357-725b3aa075fb', 'sn_sc_id' => 'd5f39088-b140-3216-b63c-4c3ca180d5f9', 'sn_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'CP', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => '3ad48b16-2582-32b9-8b18-fb94f67f2d53', 'sn_sc_id' => 'f3815363-ea4b-3748-9710-857efb4b222d', 'sn_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'REL', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
    }
}

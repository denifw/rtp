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
        DB::table('serial_number')->insert(['sn_id' => '4b12a22b-7ccb-3ab2-bb93-76e7a24fed5c', 'sn_sc_id' => '6b385708-2b66-3bd8-846e-4e3f51a995b9', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'Y', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'SI', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => '52eb56f9-0a5c-3e58-9782-3f4df78ef120', 'sn_sc_id' => '22d6ce98-5b64-30f1-a2d9-ceabedd6f124', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'Y', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'JO', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'e303cf04-6788-33f8-84af-a95f70c66b22', 'sn_sc_id' => '7d2ae74c-d95a-3cdf-bd6c-18f3c5c421e1', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'PI', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'c1bdee3a-fb32-309f-8798-50dd9b01e295', 'sn_sc_id' => '51bdb123-a54e-335d-9422-730321cb2369', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'BT', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'e5cceec1-6cbf-34b6-b4b9-4c8be82de8e0', 'sn_sc_id' => '9769e6b6-00bb-33d6-9140-5f96f94ea3b9', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'NIP', 'sn_yearly' => 'N', 'sn_monthly' => 'N', 'sn_length' => 5, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'ec2d054d-458f-3431-b126-3734f27e8a65', 'sn_sc_id' => '9769e6b6-00bb-33d6-9140-5f96f94ea3b9', 'sn_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'NIP', 'sn_yearly' => 'N', 'sn_monthly' => 'N', 'sn_length' => 5, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'b5dd5dc9-74fb-3667-b899-412dd7058105', 'sn_sc_id' => 'd5f39088-b140-3216-b63c-4c3ca180d5f9', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'CP', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'e22c78b6-9127-33ff-9876-9bcf649a1756', 'sn_sc_id' => 'f3815363-ea4b-3748-9710-857efb4b222d', 'sn_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'REL', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => 'c0bb944a-7565-31b4-a357-725b3aa075fb', 'sn_sc_id' => 'd5f39088-b140-3216-b63c-4c3ca180d5f9', 'sn_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'CP', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_number')->insert(['sn_id' => '3ad48b16-2582-32b9-8b18-fb94f67f2d53', 'sn_sc_id' => 'f3815363-ea4b-3748-9710-857efb4b222d', 'sn_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'REL', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sn_created_on' => date('Y-m-d H:i:s')]);

    }
}

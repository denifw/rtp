<?php

use Illuminate\Database\Seeder;

class SerialCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('serial_code')->insert(['sc_id' => 'd5f39088-b140-3216-b63c-4c3ca180d5f9', 'sc_code' => 'CP', 'sc_description' => 'Contact Person Number', 'sc_active' => 'Y', 'sc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_code')->insert(['sc_id' => 'f3815363-ea4b-3748-9710-857efb4b222d', 'sc_code' => 'REL', 'sc_description' => 'Relation Number', 'sc_active' => 'Y', 'sc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_code')->insert(['sc_id' => '9769e6b6-00bb-33d6-9140-5f96f94ea3b9', 'sc_code' => 'EM', 'sc_description' => 'Employee Number', 'sc_active' => 'Y', 'sc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_code')->insert(['sc_id' => '6b385708-2b66-3bd8-846e-4e3f51a995b9', 'sc_code' => 'SI', 'sc_description' => 'Sales Invoice', 'sc_active' => 'Y', 'sc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_code')->insert(['sc_id' => '51bdb123-a54e-335d-9422-730321cb2369', 'sc_code' => 'BT', 'sc_description' => 'Bank Transaction', 'sc_active' => 'Y', 'sc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_code')->insert(['sc_id' => '7d2ae74c-d95a-3cdf-bd6c-18f3c5c421e1', 'sc_code' => 'PI', 'sc_description' => 'Cash Payment', 'sc_active' => 'Y', 'sc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('serial_code')->insert(['sc_id' => '22d6ce98-5b64-30f1-a2d9-ceabedd6f124', 'sc_code' => 'JO', 'sc_description' => 'Job Order', 'sc_active' => 'Y', 'sc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'sc_created_on' => date('Y-m-d H:i:s')]);
    }
}

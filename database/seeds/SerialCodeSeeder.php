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
    }
}

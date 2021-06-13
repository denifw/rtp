<?php

use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service')->insert(['srv_id' => '373387ad-5421-3500-93e6-318a950d5bfb', 'srv_name' => 'Wholesale', 'srv_code' => 'allin', 'srv_active' => 'Y', 'srv_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'srv_created_on' => date('Y-m-d H:i:s')]);
        DB::table('service')->insert(['srv_id' => '020a60ee-d45d-34ae-9c9f-765319e9d6a7', 'srv_name' => 'Cost And Fee', 'srv_code' => 'caf', 'srv_active' => 'Y', 'srv_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'srv_created_on' => date('Y-m-d H:i:s')]);
    }
}

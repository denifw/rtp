<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currency')->insert(['cur_id' => 'aea8105b-e7e1-3b07-a419-53f356f8eac8', 'cur_cnt_id' => '3c79f2bd-d52b-3e02-85dc-c77399fcff82', 'cur_name' => 'Indonesia Rupiah', 'cur_iso' => 'IDR', 'cur_active' => 'Y', 'cur_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'cur_created_on' => date('Y-m-d H:i:s')]);
    }
}

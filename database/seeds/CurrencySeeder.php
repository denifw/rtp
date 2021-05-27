<?php

use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currency')->insert(['cur_cnt_id' => 104, 'cur_name' => 'Indonesia Rupiah', 'cur_iso' => 'IDR', 'cur_active' => 'Y', 'cur_created_on' => date('Y-m-d H:i:s'), 'cur_created_by' => 1]);
        DB::table('currency')->insert(['cur_cnt_id' => 236, 'cur_name' => 'United States Dollar', 'cur_iso' => 'USD', 'cur_active' => 'Y', 'cur_created_on' => date('Y-m-d H:i:s'), 'cur_created_by' => 1]);
        DB::table('currency')->insert(['cur_cnt_id' => 200, 'cur_name' => 'Singapore Dollar', 'cur_iso' => 'SGD', 'cur_active' => 'Y', 'cur_created_on' => date('Y-m-d H:i:s'), 'cur_created_by' => 1]);
    }
}

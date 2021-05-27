<?php

use Illuminate\Database\Seeder;

class SerialHistorySampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('serial_history')->where('sh_sn_id', 3)->update(['sh_number' => 3]);
        DB::table('serial_history')->where('sh_sn_id', 4)->update(['sh_number' => 4]);
        DB::table('serial_history')->insert(['sh_sn_id' => 9, 'sh_year' => '19', 'sh_month' => null, 'sh_number' => 6, 'sh_created_on' => date('Y-m-d H:i:s'), 'sh_created_by' => 1]);
        DB::table('serial_history')->insert(['sh_sn_id' => 5, 'sh_year' => '19', 'sh_month' => null, 'sh_number' => 1, 'sh_created_on' => date('Y-m-d H:i:s'), 'sh_created_by' => 1]);
    }
}

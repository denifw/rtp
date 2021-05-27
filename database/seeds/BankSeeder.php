<?php

use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bank')->insert(['bn_short_name' => 'BCA', 'bn_name' => 'Bank Central Asia', 'bn_active' => 'Y', 'bn_created_on' => date('Y-m-d H:i:s'), 'bn_created_by' => 1]);
        DB::table('bank')->insert(['bn_short_name' => 'BNI', 'bn_name' => 'Bank Negara Indonesia', 'bn_active' => 'Y', 'bn_created_on' => date('Y-m-d H:i:s'), 'bn_created_by' => 1]);
        DB::table('bank')->insert(['bn_short_name' => 'BRI', 'bn_name' => 'Bank Rakyat Indonesia', 'bn_active' => 'Y', 'bn_created_on' => date('Y-m-d H:i:s'), 'bn_created_by' => 1]);
        DB::table('bank')->insert(['bn_short_name' => 'MANDIRI', 'bn_name' => 'Bank Mandiri', 'bn_active' => 'Y', 'bn_created_on' => date('Y-m-d H:i:s'), 'bn_created_by' => 1]);
    }
}

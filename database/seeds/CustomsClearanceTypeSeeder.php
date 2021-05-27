<?php

use Illuminate\Database\Seeder;

class CustomsClearanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customs_clearance_type')->insert(['cct_name' => 'RED LINE', 'cct_active' => 'Y', 'cct_created_on' => date('Y-m-d H:i:s'), 'cct_created_by' => 1]);
        DB::table('customs_clearance_type')->insert(['cct_name' => 'GREEN LINE', 'cct_active' => 'Y', 'cct_created_on' => date('Y-m-d H:i:s'), 'cct_created_by' => 1]);
    }
}

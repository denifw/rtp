<?php

use Illuminate\Database\Seeder;

class CostCodeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cost_code_group')->insert(['ccg_ss_id' => 2, 'ccg_code' => '1100', 'ccg_name' => 'Sales Trucking', 'ccg_srv_id' => 3, 'ccg_type' => 'S', 'ccg_active' => 'Y', 'ccg_created_on' => date('Y-m-d H:i:s'), 'ccg_created_by' => 1]);
        DB::table('cost_code_group')->insert(['ccg_ss_id' => 2, 'ccg_code' => '1200', 'ccg_name' => 'Purchase Trucking', 'ccg_srv_id' => 3, 'ccg_type' => 'P', 'ccg_active' => 'Y', 'ccg_created_on' => date('Y-m-d H:i:s'), 'ccg_created_by' => 1]);
        DB::table('cost_code_group')->insert(['ccg_ss_id' => 2, 'ccg_code' => '1300', 'ccg_name' => 'Reimburse Trucking', 'ccg_srv_id' => 3, 'ccg_type' => 'R', 'ccg_active' => 'Y', 'ccg_created_on' => date('Y-m-d H:i:s'), 'ccg_created_by' => 1]);
    }
}

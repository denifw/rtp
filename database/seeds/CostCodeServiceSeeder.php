<?php

use Illuminate\Database\Seeder;

class CostCodeServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 1, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 3, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 2, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 4, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 5, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 6, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 7, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 8, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 9, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
        DB::table('cost_code_service')->insert(['ccs_cc_id' => 10, 'ccs_srv_id' => 1, 'ccs_active' => 'Y', 'ccs_created_on' => date('Y-m-d H:i:s'), 'ccs_created_by' => 1]);
    }
}

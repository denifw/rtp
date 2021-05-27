<?php

use Illuminate\Database\Seeder;

class CostCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cost_code')->insert(['cc_ss_id' => 2, 'cc_code' => '1101', 'cc_name' => 'Jasa Trucking', 'cc_ccg_id' => 1, 'cc_active' => 'Y', 'cc_created_on' => date('Y-m-d H:i:s'), 'cc_created_by' => 1]);
        DB::table('cost_code')->insert(['cc_ss_id' => 2, 'cc_code' => '1201', 'cc_name' => 'Uang Jalan', 'cc_ccg_id' => 2, 'cc_active' => 'Y', 'cc_created_on' => date('Y-m-d H:i:s'), 'cc_created_by' => 1]);
        DB::table('cost_code')->insert(['cc_ss_id' => 2, 'cc_code' => '1202', 'cc_name' => 'Penyebrangan', 'cc_ccg_id' => 2, 'cc_active' => 'Y', 'cc_created_on' => date('Y-m-d H:i:s'), 'cc_created_by' => 1]);
        DB::table('cost_code')->insert(['cc_ss_id' => 2, 'cc_code' => '1301', 'cc_name' => 'LOLO', 'cc_ccg_id' => 3, 'cc_active' => 'Y', 'cc_created_on' => date('Y-m-d H:i:s'), 'cc_created_by' => 1]);
    }
}

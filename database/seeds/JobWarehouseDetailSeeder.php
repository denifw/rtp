<?php

use Illuminate\Database\Seeder;

class JobWarehouseDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('job_warehouse_detail')->insert(['jwd_jow_id' => 1, 'jwd_jog_id' => 1, 'jwd_whs_id' => 1, 'jwd_quantity' => 500, 'jwd_uom_id' => 3, 'jwd_created_on' => date('Y-m-d H:i:s'), 'jwd_created_by' => 1]);
        DB::table('job_warehouse_detail')->insert(['jwd_jow_id' => 1, 'jwd_jog_id' => 1, 'jwd_whs_id' => 2, 'jwd_quantity' => 500, 'jwd_uom_id' => 3, 'jwd_created_on' => date('Y-m-d H:i:s'), 'jwd_created_by' => 1]);
        DB::table('job_warehouse_detail')->insert(['jwd_jow_id' => 1, 'jwd_jog_id' => 1, 'jwd_whs_id' => 3, 'jwd_quantity' => 500, 'jwd_uom_id' => 3, 'jwd_created_on' => date('Y-m-d H:i:s'), 'jwd_created_by' => 1]);
    }
}

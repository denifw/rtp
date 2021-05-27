<?php

use Illuminate\Database\Seeder;

class StockAdjustmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('stock_adjustment_type')->insert(['sat_ss_id' => 2, 'sat_code' => 'SA0001', 'sat_description' => 'Goods Missing', 'sat_active' => 'Y', 'sat_created_on' => date('Y-m-d H:i:s'), 'sat_created_by' => 1]);
        DB::table('stock_adjustment_type')->insert(['sat_ss_id' => 2, 'sat_code' => 'SA0002', 'sat_description' => 'Wrong Outbound', 'sat_active' => 'Y', 'sat_created_on' => date('Y-m-d H:i:s'), 'sat_created_by' => 1]);
    }
}

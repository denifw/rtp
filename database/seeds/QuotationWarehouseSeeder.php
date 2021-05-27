<?php

use Illuminate\Database\Seeder;

class QuotationWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('quotation_warehouse')->insert(['qtnw_qtn_id' => 2, 'qtnw_wh_id' => 1, 'qtnw_created_on' => date('Y-m-d H:i:s'), 'qtnw_created_by' => 1]);
        DB::table('quotation_warehouse')->insert(['qtnw_qtn_id' => 3, 'qtnw_wh_id' => 1, 'qtnw_created_on' => date('Y-m-d H:i:s'), 'qtnw_created_by' => 1]);
        DB::table('quotation_warehouse')->insert(['qtnw_qtn_id' => 4, 'qtnw_wh_id' => 1, 'qtnw_created_on' => date('Y-m-d H:i:s'), 'qtnw_created_by' => 1]);
        DB::table('quotation_warehouse')->insert(['qtnw_qtn_id' => 5, 'qtnw_wh_id' => 1, 'qtnw_created_on' => date('Y-m-d H:i:s'), 'qtnw_created_by' => 1]);
        DB::table('quotation_warehouse')->insert(['qtnw_qtn_id' => 6, 'qtnw_wh_id' => 1, 'qtnw_created_on' => date('Y-m-d H:i:s'), 'qtnw_created_by' => 1]);
    }
}

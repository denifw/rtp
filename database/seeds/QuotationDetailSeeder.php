<?php

use Illuminate\Database\Seeder;

class QuotationDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('quotation_detail')->insert(['qtnd_qtn_id' => 1, 'qtnd_cc_id' => 3, 'qtnd_description' => 'Sewa Gudang', 'qtnd_quantity' => 1, 'qtnd_rate' => 150000, 'qtnd_minimum_rate' => null, 'qtnd_uom_id' => 7, 'qtnd_exchange_rate' => 1, 'qtnd_cur_id' => 1, 'qtnd_tax_id' => 1, 'qtnd_created_on' => date('Y-m-d H:i:s'), 'qtnd_created_by' => 1]);
        DB::table('quotation_detail')->insert(['qtnd_qtn_id' => 2, 'qtnd_cc_id' => 4, 'qtnd_description' => 'Jasa Buruh', 'qtnd_quantity' => 1, 'qtnd_rate' => 5500, 'qtnd_minimum_rate' => null, 'qtnd_uom_id' => 3, 'qtnd_exchange_rate' => 1, 'qtnd_cur_id' => 1, 'qtnd_tax_id' => 1, 'qtnd_created_on' => date('Y-m-d H:i:s'), 'qtnd_created_by' => 1]);
        DB::table('quotation_detail')->insert(['qtnd_qtn_id' => 3, 'qtnd_cc_id' => 5, 'qtnd_description' => 'Jasa Opname', 'qtnd_quantity' => 1, 'qtnd_rate' => 3300, 'qtnd_minimum_rate' => null, 'qtnd_uom_id' => 3, 'qtnd_exchange_rate' => 1, 'qtnd_cur_id' => 1, 'qtnd_tax_id' => 1, 'qtnd_created_on' => date('Y-m-d H:i:s'), 'qtnd_created_by' => 1]);
        DB::table('quotation_detail')->insert(['qtnd_qtn_id' => 4, 'qtnd_cc_id' => 3, 'qtnd_description' => 'Jasa Bongkar Muat', 'qtnd_quantity' => 1, 'qtnd_rate' => 3400, 'qtnd_minimum_rate' => null, 'qtnd_uom_id' => 3, 'qtnd_exchange_rate' => 1, 'qtnd_cur_id' => 1, 'qtnd_tax_id' => 1, 'qtnd_created_on' => date('Y-m-d H:i:s'), 'qtnd_created_by' => 1]);
        DB::table('quotation_detail')->insert(['qtnd_qtn_id' => 5, 'qtnd_cc_id' => 3, 'qtnd_description' => 'Sewa Gudang', 'qtnd_quantity' => 1, 'qtnd_rate' => 160000, 'qtnd_minimum_rate' => null, 'qtnd_uom_id' => 7, 'qtnd_exchange_rate' => 1, 'qtnd_cur_id' => 1, 'qtnd_tax_id' => 1, 'qtnd_created_on' => date('Y-m-d H:i:s'), 'qtnd_created_by' => 1]);
    }
}

<?php

use Illuminate\Database\Seeder;

class QuotationRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('quotation_request')->insert(['qtnr_qtn_id' => 2, 'qtnr_requested_by' => 1, 'qtnr_reject_reason' => '', 'qtnr_created_on' => date('Y-m-d H:i:s'), 'qtnr_created_by' => 1]);
        DB::table('quotation_request')->insert(['qtnr_qtn_id' => 3, 'qtnr_requested_by' => 1, 'qtnr_reject_reason' => '', 'qtnr_created_on' => date('Y-m-d H:i:s'), 'qtnr_created_by' => 1]);
    }
}

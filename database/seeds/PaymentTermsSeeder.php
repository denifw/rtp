<?php

use Illuminate\Database\Seeder;

class PaymentTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_terms')->insert(['pt_ss_id' => 2, 'pt_days' => 7, 'pt_name' => '7 Days', 'pt_active' => 'Y', 'pt_created_on' => date('Y-m-d H:i:s'), 'pt_created_by' => 1]);
        DB::table('payment_terms')->insert(['pt_ss_id' => 2, 'pt_days' => 14, 'pt_name' => '14 Days', 'pt_active' => 'Y', 'pt_created_on' => date('Y-m-d H:i:s'), 'pt_created_by' => 1]);
        DB::table('payment_terms')->insert(['pt_ss_id' => 2, 'pt_days' => 30, 'pt_name' => '30 Days', 'pt_active' => 'Y', 'pt_created_on' => date('Y-m-d H:i:s'), 'pt_created_by' => 1]);
        DB::table('payment_terms')->insert(['pt_ss_id' => 2, 'pt_days' => 60, 'pt_name' => '60 Days', 'pt_active' => 'Y', 'pt_created_on' => date('Y-m-d H:i:s'), 'pt_created_by' => 1]);
        DB::table('payment_terms')->insert(['pt_ss_id' => 2, 'pt_days' => 90, 'pt_name' => '90 Days', 'pt_active' => 'Y', 'pt_created_on' => date('Y-m-d H:i:s'), 'pt_created_by' => 1]);
        DB::table('payment_terms')->insert(['pt_ss_id' => 2, 'pt_days' => 120, 'pt_name' => '120 Days', 'pt_active' => 'Y', 'pt_created_on' => date('Y-m-d H:i:s'), 'pt_created_by' => 1]);
    }
}

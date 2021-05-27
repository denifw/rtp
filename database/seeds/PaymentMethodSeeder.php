<?php

use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_method')->insert(['pm_ss_id' => 2, 'pm_name' => 'Bank Transfer', 'pm_active' => 'Y', 'pm_created_on' => date('Y-m-d H:i:s'), 'pm_created_by' => 1]);
        DB::table('payment_method')->insert(['pm_ss_id' => 2, 'pm_name' => 'Cash', 'pm_active' => 'Y', 'pm_created_on' => date('Y-m-d H:i:s'), 'pm_created_by' => 1]);
        DB::table('payment_method')->insert(['pm_ss_id' => 2, 'pm_name' => 'Check', 'pm_active' => 'Y', 'pm_created_on' => date('Y-m-d H:i:s'), 'pm_created_by' => 1]);
    }
}

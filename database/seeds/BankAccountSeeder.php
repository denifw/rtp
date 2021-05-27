<?php

use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bank_account')->insert(['ba_ss_id' => 2, 'ba_code' => '1-100001', 'ba_description' => 'Kas Besar Pemasukan IDR', 'ba_bn_id' => 1, 'ba_cur_id' => 1, 'ba_account_number' => '1111111111', 'ba_account_name' => 'PT Makmur Berkat Solusi', 'ba_main' => 'Y', 'ba_receivable' => 'Y', 'ba_payable' => 'N', 'ba_uid' => 'c447416c-fbdd-3390-8938-71e4f5a8b4c6', 'ba_created_on' => date('Y-m-d H:i:s'), 'ba_created_by' => 1]);
        DB::table('bank_account')->insert(['ba_ss_id' => 2, 'ba_code' => '1-100002', 'ba_description' => 'Kas Besar Pemasukan USD', 'ba_bn_id' => 1, 'ba_cur_id' => 2, 'ba_account_number' => '2222222222', 'ba_account_name' => 'PT Makmur Berkat Solusi', 'ba_main' => 'Y', 'ba_receivable' => 'Y', 'ba_payable' => 'N', 'ba_uid' => 'bafc15a0-562e-3898-a7d3-e3e632b84385', 'ba_created_on' => date('Y-m-d H:i:s'), 'ba_created_by' => 1]);
        DB::table('bank_account')->insert(['ba_ss_id' => 2, 'ba_code' => '1-200001', 'ba_description' => 'Kas Besar Pengeluaran IDR', 'ba_bn_id' => 1, 'ba_cur_id' => 1, 'ba_account_number' => '3333333333', 'ba_account_name' => 'PT Makmur Berkat Solusi', 'ba_main' => 'Y', 'ba_receivable' => 'N', 'ba_payable' => 'Y', 'ba_uid' => '33befb05-a759-383e-9e30-25b8c36e6c12', 'ba_created_on' => date('Y-m-d H:i:s'), 'ba_created_by' => 1]);
        DB::table('bank_account')->insert(['ba_ss_id' => 2, 'ba_code' => '1-300001', 'ba_description' => 'KAS Joni', 'ba_bn_id' => 1, 'ba_cur_id' => 1, 'ba_account_number' => '4444444444', 'ba_account_name' => 'Joni', 'ba_main' => 'N', 'ba_receivable' => 'Y', 'ba_payable' => 'Y', 'ba_us_id' => 129, 'ba_limit' => 3000000, 'ba_uid' => '648e50fb-79c4-303f-8457-11755cdfeec4', 'ba_created_on' => date('Y-m-d H:i:s'), 'ba_created_by' => 1]);
        DB::table('bank_account')->insert(['ba_ss_id' => 2, 'ba_code' => '1-300002', 'ba_description' => 'KAS Aping', 'ba_bn_id' => 1, 'ba_cur_id' => 1, 'ba_account_number' => '5555555555', 'ba_account_name' => 'Aping', 'ba_main' => 'N', 'ba_receivable' => 'Y', 'ba_payable' => 'Y', 'ba_us_id' => 128, 'ba_uid' => 'd40f8967-303f-30f3-acb0-b3ddb1ae1100', 'ba_created_on' => date('Y-m-d H:i:s'), 'ba_created_by' => 1]);
    }
}

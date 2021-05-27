<?php

use Illuminate\Database\Seeder;

class SystemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('system_table')->where('st_id', 109)->update([
            'st_name' => 'Bank Account',
            'st_prefix' => 'ba',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->where('st_id', 110)->update([
            'st_name' => 'Bank Account Balance',
            'st_prefix' => 'bab',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->where('st_id', 111)->update([
            'st_name' => 'Bank Transaction',
            'st_prefix' => 'bt',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->where('st_id', 112)->update([
            'st_name' => 'Bank Transaction Approval',
            'st_prefix' => 'bta',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->where('st_id', 113)->update([
            'st_name' => 'Cash Advance',
            'st_prefix' => 'ca',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->where('st_id', 114)->update([
            'st_name' => 'Cash Advance Received',
            'st_prefix' => 'crc',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->where('st_id', 115)->update([
            'st_name' => 'Cash Advance Returned',
            'st_prefix' => 'crt',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->where('st_id', 180)->update([
            'st_name' => 'Cash Advance Detail',
            'st_prefix' => 'cad',
            'st_path' => 'Finance/CashAndBank',
            'st_active' => 'Y',
        ]);
        DB::table('system_table')->insert(['st_name' => 'Electronic Account', 'st_prefix' => 'ea', 'st_path' => 'Finance/CashAndBank', 'st_active' => 'Y', 'st_uid' => '560b1b47-315a-391d-8ea1-827721e71e15', 'st_created_on' => date('Y-m-d H:i:s'), 'st_created_by' => 1]);
        DB::table('system_table')->insert(['st_name' => 'Electronic Balance', 'st_prefix' => 'eb', 'st_path' => 'Finance/CashAndBank', 'st_active' => 'Y', 'st_uid' => '0e92dc0d-9c88-3a73-8903-56b287df95b5', 'st_created_on' => date('Y-m-d H:i:s'), 'st_created_by' => 1]);
        DB::table('system_table')->insert(['st_name' => 'Electronic Top Up', 'st_prefix' => 'et', 'st_path' => 'Finance/CashAndBank', 'st_active' => 'Y', 'st_uid' => '87927bd1-1a3e-34bf-be0f-d3559c3b1cbf', 'st_created_on' => date('Y-m-d H:i:s'), 'st_created_by' => 1]);
        DB::table('system_table')->insert(['st_name' => 'Electronic Payment', 'st_prefix' => 'ep', 'st_path' => 'Finance/CashAndBank', 'st_active' => 'Y', 'st_uid' => '1731c852-dcfe-3f3d-bfae-d1afc6f8567f', 'st_created_on' => date('Y-m-d H:i:s'), 'st_created_by' => 1]);
    }
}

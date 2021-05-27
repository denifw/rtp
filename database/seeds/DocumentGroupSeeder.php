<?php

use Illuminate\Database\Seeder;

class DocumentGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('document_group')->where('dcg_id', 6)->update([
            'dcg_code' => 'bt',
            'dcg_description' => 'Bank Transaction',
            'dcg_table' => 'bank_transaction',
            'dcg_value_field' => 'bt_id',
            'dcg_text_field' => 'bt_number'
        ]);
        DB::table('document_group')->where('dcg_id', 7)->update([
            'dcg_code' => 'ca',
            'dcg_description' => 'Cash Payment',
        ]);
        DB::table('document_group')->insert(['dcg_code' => 'ea', 'dcg_description' => 'Electronic Account', 'dcg_table' => 'electronic_account', 'dcg_value_field' => 'ea_id', 'dcg_text_field' => 'ea_code', 'dcg_active' => 'Y', 'dcg_uid' => 'a9a2f830-738a-35b6-b406-ef1a3276f199', 'dcg_created_on' => date('Y-m-d H:i:s'), 'dcg_created_by' => 1]);
    }
}

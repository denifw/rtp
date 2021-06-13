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
        DB::table('document_group')->insert(['dcg_id' => '1b3e105d-b7c7-35b8-a885-9e2d984adba4', 'dcg_code' => 'ss', 'dcg_description' => 'System Setting', 'dcg_table' => 'system_setting', 'dcg_value_field' => 'ss_id', 'dcg_text_field' => 'ss_relation', 'dcg_active' => 'Y', 'dcg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dcg_created_on' => date('Y-m-d H:i:s')]);
    }
}

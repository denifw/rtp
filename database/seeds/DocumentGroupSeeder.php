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
        DB::table('document_group')->insert(['dcg_id' => '9ca9ffb4-c43a-3c6b-a28a-1d0c28d04951', 'dcg_code' => 'jo', 'dcg_description' => 'Job Order', 'dcg_table' => 'job_order', 'dcg_value_field' => 'jo_id', 'dcg_text_field' => 'jo_number', 'dcg_active' => 'Y', 'dcg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dcg_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_group')->insert(['dcg_id' => '5caecd85-23f3-3da0-8f01-a280d929d49a', 'dcg_code' => 'em', 'dcg_description' => 'Employee', 'dcg_table' => 'employee', 'dcg_value_field' => 'em_id', 'dcg_text_field' => 'em_number', 'dcg_active' => 'Y', 'dcg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dcg_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_group')->insert(['dcg_id' => 'b01180c3-865a-357b-a937-7eeefa098c42', 'dcg_code' => 'rel', 'dcg_description' => 'Document Relation', 'dcg_table' => 'relation', 'dcg_value_field' => 'rel_id', 'dcg_text_field' => 'rel_name', 'dcg_active' => 'Y', 'dcg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dcg_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_group')->insert(['dcg_id' => '1b3e105d-b7c7-35b8-a885-9e2d984adba4', 'dcg_code' => 'ss', 'dcg_description' => 'System Setting', 'dcg_table' => 'system_setting', 'dcg_value_field' => 'ss_id', 'dcg_text_field' => 'ss_relation', 'dcg_active' => 'Y', 'dcg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dcg_created_on' => date('Y-m-d H:i:s')]);

    }
}

<?php

use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_type')->insert(['dct_id' => '9594d5b7-2443-3c9c-885a-26b0c35f3ca9', 'dct_dcg_id' => '9ca9ffb4-c43a-3c6b-a28a-1d0c28d04951', 'dct_code' => 'jop', 'dct_description' => 'Job Progress', 'dct_table' => 'job_progress', 'dct_value_field' => 'jop_id', 'dct_text_field' => 'jop_date', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '8551e4aa-16a2-34e6-9c69-118033aa4975', 'dct_dcg_id' => '9ca9ffb4-c43a-3c6b-a28a-1d0c28d04951', 'dct_code' => 'design', 'dct_description' => 'Design', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '8eb7a660-4eb3-34f9-b3ea-eef0404c08c3', 'dct_dcg_id' => '5caecd85-23f3-3da0-8f01-a280d929d49a', 'dct_code' => 'picture', 'dct_description' => 'Picture', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => 'a6f53ff9-1249-3d68-b35f-7c8edf86e5f8', 'dct_dcg_id' => '5caecd85-23f3-3da0-8f01-a280d929d49a', 'dct_code' => 'sim', 'dct_description' => 'Driver License', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '1d93d4e2-24e6-3432-a956-4184312c2377', 'dct_dcg_id' => '5caecd85-23f3-3da0-8f01-a280d929d49a', 'dct_code' => 'ktp', 'dct_description' => 'Identity Card', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '6eaa1b0a-d58c-36a3-af0c-7b6530afa3a8', 'dct_dcg_id' => 'b01180c3-865a-357b-a937-7eeefa098c42', 'dct_code' => 'logo', 'dct_description' => 'Relation Logo', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '15d918b7-0df3-3845-9f8f-a07bfaae2926', 'dct_dcg_id' => '1b3e105d-b7c7-35b8-a885-9e2d984adba4', 'dct_code' => 'icon', 'dct_description' => 'System Icon', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '5efc6691-4ac7-3da5-9425-4f394188b220', 'dct_dcg_id' => '1b3e105d-b7c7-35b8-a885-9e2d984adba4', 'dct_code' => 'logo', 'dct_description' => 'System Logo', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '6b4f06e3-f428-36b9-8c22-362558b0ae3f', 'dct_dcg_id' => 'ff6caf80-142f-3a1e-b2c0-c16350e84ca3', 'dct_code' => 'receipt', 'dct_description' => 'Receipt', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document_type')->insert(['dct_id' => '6df05b33-c417-3d8e-85e2-6ce1355b4af5', 'dct_dcg_id' => '8193240c-7b58-34c0-9254-cb5f21d96e7a', 'dct_code' => 'receipt', 'dct_description' => 'Receipt Transfer', 'dct_active' => 'Y', 'dct_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'dct_created_on' => date('Y-m-d H:i:s')]);
    }
}

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
        DB::table('system_table')->insert(['st_id' => '7bb5e5d1-c5dd-3474-95c8-1be1162e5774', 'st_name' => 'UserMapping', 'st_prefix' => 'ump', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => 'ebf5dad7-adc6-32a9-aee5-ebe420e2be12', 'st_name' => 'Users', 'st_prefix' => 'us', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '605c9ed8-562f-3b6c-ade7-7fc4acfbfabe', 'st_name' => 'User Group Api Access', 'st_prefix' => 'uga', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => 'bc49b432-e611-3598-ad54-c94f23093815', 'st_name' => 'User Group Right', 'st_prefix' => 'ugr', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '20220864-179c-3233-88e8-0053b8563400', 'st_name' => 'User Group Page', 'st_prefix' => 'ugp', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '4506b07e-8c64-3a7f-9e3c-702e4074fa7c', 'st_name' => 'User Group Detail', 'st_prefix' => 'ugd', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => 'fa2e141e-6615-3f6a-ae82-40ad676f4664', 'st_name' => 'User Group', 'st_prefix' => 'usg', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '048b7b4a-1b38-3f74-aa2e-517e787dcee5', 'st_name' => 'Document Template', 'st_prefix' => 'dt', 'st_path' => 'System/Document', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '0df45886-8aaa-397e-afce-21c7d6b68885', 'st_name' => 'Page Category', 'st_prefix' => 'pc', 'st_path' => 'System/Page', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '35dff266-e78f-33a0-9287-618d2f5ef1d6', 'st_name' => 'Document Type', 'st_prefix' => 'dct', 'st_path' => 'System/Document', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '50077c87-89a4-3602-8c15-313199a8f83e', 'st_name' => 'Menu', 'st_prefix' => 'mn', 'st_path' => 'System/Page', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '6b6dc19a-376e-38bc-8c88-ea519c88b108', 'st_name' => 'Page', 'st_prefix' => 'pg', 'st_path' => 'System/Page', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '845ae800-e63b-3d67-b721-0ff5e556209c', 'st_name' => 'Document Group', 'st_prefix' => 'dcg', 'st_path' => 'System/Document', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '95e3366e-2a1b-3a8d-bcb6-1704d11f1fd7', 'st_name' => 'Page Right', 'st_prefix' => 'pr', 'st_path' => 'System/Page', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => 'df883e83-62f4-3ca1-9f98-3859b6bbe14c', 'st_name' => 'Document Template Type', 'st_prefix' => 'dtt', 'st_path' => 'System/Document', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => 'f58192d1-42b5-3f0f-bdfa-db9f3b147089', 'st_name' => 'System Table', 'st_prefix' => 'st', 'st_path' => 'System/Page', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => 'fe19d08f-1d1f-370e-9e0d-cc367b274ce4', 'st_name' => 'Serial Number', 'st_prefix' => 'sn', 'st_path' => 'System/Access', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '8ffa4567-bd54-3b80-94c1-b7aff0bebb5d', 'st_name' => 'Office', 'st_prefix' => 'of', 'st_path' => 'Crm', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '662796df-3008-38b2-a7ae-191866bae5ce', 'st_name' => 'Contact Person', 'st_prefix' => 'cp', 'st_path' => 'Crm', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
        DB::table('system_table')->insert(['st_id' => '30755bda-0d6a-3e29-bee2-eb7f94f809b0', 'st_name' => 'Relation', 'st_prefix' => 'rel', 'st_path' => 'Crm', 'st_active' => 'Y', 'st_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'st_created_on' => date('Y-m-d H:i:s')]);
    }
}

<?php

use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document')->insert(['doc_id' => '5cf4f24b-d885-3464-bb00-185ffb8c4480', 'doc_dct_id' => '5efc6691-4ac7-3da5-9425-4f394188b220', 'doc_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'doc_group_reference' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'doc_file_name' => '1623579549.png', 'doc_description' => '1623579549.png', 'doc_file_size' => '70735', 'doc_file_type' => 'png', 'doc_public' => 'N', 'doc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'doc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document')->insert(['doc_id' => '7c931e7b-8351-3d6d-bed3-11c68c964558', 'doc_dct_id' => '15d918b7-0df3-3845-9f8f-a07bfaae2926', 'doc_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'doc_group_reference' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'doc_file_name' => '1623579549.png', 'doc_description' => '1623579549.png', 'doc_file_size' => '70735', 'doc_file_type' => 'png', 'doc_public' => 'N', 'doc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'doc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document')->insert(['doc_id' => '4facc460-0695-3d8f-b2d1-3d68a6c612c7', 'doc_dct_id' => '5efc6691-4ac7-3da5-9425-4f394188b220', 'doc_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'doc_group_reference' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'doc_file_name' => '1623578874.png', 'doc_description' => '1623578874.png', 'doc_file_size' => '15058', 'doc_file_type' => 'png', 'doc_public' => 'N', 'doc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'doc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('document')->insert(['doc_id' => 'b2101392-f23c-3658-9e3b-3ee09cd2e89f', 'doc_dct_id' => '15d918b7-0df3-3845-9f8f-a07bfaae2926', 'doc_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'doc_group_reference' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'doc_file_name' => '1623578874.png', 'doc_description' => '1623578874.png', 'doc_file_size' => '15058', 'doc_file_type' => 'png', 'doc_public' => 'N', 'doc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'doc_created_on' => date('Y-m-d H:i:s')]);
    }
}

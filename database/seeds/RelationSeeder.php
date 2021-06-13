<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class RelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('relation')->insert(['rel_id' => '07fb49e6-8d87-332e-a3e2-9fec83b597d4', 'rel_ss_id' => 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'rel_name' => 'PT Nusantara Construction', 'rel_number' => 'REL-210100001', 'rel_short_name' => 'NC', 'rel_of_id' => '72a86af8-64b6-374d-8ada-de8664243a4e', 'rel_cp_id' => '67d3d4e8-6872-345a-ac9b-2268db54f193', 'rel_active' => 'Y', 'rel_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'rel_created_on' => date('Y-m-d H:i:s')]);
        DB::table('relation')->insert(['rel_id' => 'f545a902-b73b-3658-a752-db5ece6cdb08', 'rel_ss_id' => '2dbef151-3fd3-37e2-9fad-33635f3fc81a', 'rel_name' => 'System Administrator', 'rel_number' => 'REL-210100001', 'rel_short_name' => 'SYA', 'rel_of_id' => '4816ddf5-ae84-3594-9de1-b16dd8df96ce', 'rel_cp_id' => '1d421d8c-bffe-3fb8-b7f6-8b4ee79f740a', 'rel_active' => 'Y', 'rel_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'rel_created_on' => date('Y-m-d H:i:s')]);
    }
}

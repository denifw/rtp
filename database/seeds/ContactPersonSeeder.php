<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class ContactPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contact_person')->insert(['cp_id' => '1d421d8c-bffe-3fb8-b7f6-8b4ee79f740a', 'cp_number' => 'CP-210100001', 'cp_name' => 'System Admin', 'cp_email' => 'system@qomteq.com', 'cp_of_id' => '4816ddf5-ae84-3594-9de1-b16dd8df96ce', 'cp_active' => 'Y', 'cp_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'cp_created_on' => date('Y-m-d H:i:s')]);
        DB::table('contact_person')->insert(['cp_id' => '67d3d4e8-6872-345a-ac9b-2268db54f193', 'cp_number' => 'CP-210100001', 'cp_name' => 'Deni Firdaus Waruwu', 'cp_email' => 'deni.firdaus.w@gmail.com', 'cp_of_id' => '72a86af8-64b6-374d-8ada-de8664243a4e', 'cp_active' => 'Y', 'cp_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'cp_created_on' => date('Y-m-d H:i:s')]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class PageCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('page_category')->insert(['pc_id' => '3de0c475-3e53-3afb-a664-8a571429f998', 'pc_name' => 'Statistic', 'pc_code' => 'statistic', 'pc_active' => 'Y', 'pc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'pc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('page_category')->insert(['pc_id' => '53c93ae7-3ee9-3ef6-abca-970753d4be67', 'pc_name' => 'Dashboard', 'pc_code' => 'dashboard', 'pc_active' => 'Y', 'pc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'pc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('page_category')->insert(['pc_id' => '8c14654c-c36b-359c-8f34-b4c624488324', 'pc_name' => 'Detail', 'pc_code' => 'detail', 'pc_route' => 'detail', 'pc_active' => 'Y', 'pc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'pc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('page_category')->insert(['pc_id' => '93a8ab9a-2d4d-394d-a618-f0d6fe618308', 'pc_name' => 'Listing', 'pc_code' => 'listing', 'pc_route' => 'listing', 'pc_active' => 'Y', 'pc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'pc_created_on' => date('Y-m-d H:i:s')]);
        DB::table('page_category')->insert(['pc_id' => 'c9d1fa8c-9725-387a-8efc-2727c3176c54', 'pc_name' => 'Viewer', 'pc_code' => 'view', 'pc_route' => 'view', 'pc_active' => 'Y', 'pc_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'pc_created_on' => date('Y-m-d H:i:s')]);
    }
}

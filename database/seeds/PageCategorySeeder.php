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
        DB::table('page_category')->insert(['pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc1'), 'pc_name' => 'Dashboard', 'pc_route' => '', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page_category')->insert(['pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc2'), 'pc_name' => 'Listing', 'pc_route' => 'listing', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page_category')->insert(['pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc3'), 'pc_name' => 'Detail', 'pc_route' => 'detail', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page_category')->insert(['pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc4'), 'pc_name' => 'Viewer', 'pc_route' => 'view', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page_category')->insert(['pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc5'), 'pc_name' => 'Statistic', 'pc_route' => '', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
    }
}

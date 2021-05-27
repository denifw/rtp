<?php

use Illuminate\Database\Seeder;

class PageCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('page_category')->insert(['pc_name' => 'Dashboard', 'pc_route' => '', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => 1]);
        DB::table('page_category')->insert(['pc_name' => 'Listing', 'pc_route' => '', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => 1]);
        DB::table('page_category')->insert(['pc_name' => 'Detail', 'pc_route' => 'detail', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => 1]);
        DB::table('page_category')->insert(['pc_name' => 'Viewer', 'pc_route' => 'view', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => 1]);
        DB::table('page_category')->insert(['pc_name' => 'Statistic', 'pc_route' => '', 'pc_active' => 'Y', 'pc_created_on' => date('Y-m-d H:i:s'), 'pc_created_by' => 1]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg1'), 'pg_title' => 'Dashboard', 'pg_description' => 'My Dashboard', 'pg_route' => 'home', 'pg_mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn1'), 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc1'), 'pg_icon' => 'fa fa-tasks', 'pg_order' => 1, 'pg_default' => 'Y', 'pg_system' => 'N', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        # System - Page
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg2'), 'pg_title' => 'Table', 'pg_description' => 'List of Table', 'pg_route' => 'st', 'pg_mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn16'), 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc2'), 'pg_icon' => 'fa fa-tasks', 'pg_order' => 1, 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg3'), 'pg_title' => 'Table', 'pg_description' => 'Detail of Table', 'pg_route' => 'st', 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc3'), 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg4'), 'pg_title' => 'Menu', 'pg_description' => 'List of Menu', 'pg_route' => 'mn', 'pg_mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn16'), 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc2'), 'pg_icon' => 'fa fa-tasks', 'pg_order' => 2, 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg5'), 'pg_title' => 'Menu', 'pg_description' => 'Detail of Menu', 'pg_route' => 'mn', 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc3'), 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg6'), 'pg_title' => 'Page Category', 'pg_description' => 'List Page Category', 'pg_route' => 'pc', 'pg_mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn16'), 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc2'), 'pg_icon' => 'fa fa-tasks', 'pg_order' => 3, 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg7'), 'pg_title' => 'Page Category', 'pg_description' => 'Detail Page Category', 'pg_route' => 'pc', 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc3'), 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg8'), 'pg_title' => 'Page', 'pg_description' => 'List Page', 'pg_route' => 'pg', 'pg_mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn16'), 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc2'), 'pg_icon' => 'fa fa-tasks', 'pg_order' => 4, 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg9'), 'pg_title' => 'Page', 'pg_description' => 'Detail Page', 'pg_route' => 'pg', 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc3'), 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg10'), 'pg_title' => 'Page Access', 'pg_description' => 'List of Right', 'pg_route' => 'pr', 'pg_mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn16'), 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc2'), 'pg_icon' => 'fa fa-tasks', 'pg_order' => 5, 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg11'), 'pg_title' => 'Page Access', 'pg_description' => 'Detail of Right', 'pg_route' => 'pr', 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc3'), 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg12'), 'pg_title' => 'Api Access', 'pg_description' => 'List of API', 'pg_route' => 'aa', 'pg_mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn16'), 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc2'), 'pg_icon' => 'fa fa-tasks', 'pg_order' => 6, 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
        DB::table('page')->insert(['pg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pg13'), 'pg_title' => 'Api Access', 'pg_description' => 'Detail of API', 'pg_route' => 'aa', 'pg_pc_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'pc3'), 'pg_default' => 'N', 'pg_system' => 'Y', 'pg_active' => 'Y', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
    }
}

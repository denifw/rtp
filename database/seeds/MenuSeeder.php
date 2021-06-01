<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn1'),
            'mn_name' => 'Root',
            'mn_code' => 'root',
            'mn_order' => 1,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn2'),
            'mn_name' => 'System',
            'mn_code' => 'system',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn1'),
            'mn_order' => 100,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn3'),
            'mn_name' => 'Page',
            'mn_code' => 'page',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn2'),
            'mn_order' => 1,
            'mn_icon' => 'fa fa-tasks',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
    }
}

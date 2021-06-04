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
            'mn_name' => 'Statistic',
            'mn_code' => 'statistic',
            'mn_order' => 2,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn3'),
            'mn_name' => 'Operation',
            'mn_code' => 'operation',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn2'),
            'mn_order' => 1,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn4'),
            'mn_name' => 'Finance',
            'mn_code' => 'finance',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn2'),
            'mn_order' => 2,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn5'),
            'mn_name' => 'CRM',
            'mn_code' => 'crm',
            'mn_order' => 3,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn6'),
            'mn_name' => 'Operation',
            'mn_code' => 'operation',
            'mn_order' => 4,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn7'),
            'mn_name' => 'Administration',
            'mn_code' => 'administration',
            'mn_order' => 5,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn8'),
            'mn_name' => 'Master',
            'mn_code' => 'master',
            'mn_order' => 6,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn9'),
            'mn_name' => 'Finance',
            'mn_code' => 'finance',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn8'),
            'mn_order' => 1,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn10'),
            'mn_name' => 'Address',
            'mn_code' => 'address',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn8'),
            'mn_order' => 2,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn11'),
            'mn_name' => 'System',
            'mn_code' => 'system',
            'mn_order' => 100,
            'mn_icon' => 'fa fa-sitemap',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn12'),
            'mn_name' => 'Settings',
            'mn_code' => 'settings',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn11'),
            'mn_order' => 1,
            'mn_icon' => 'fa fa-tasks',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn13'),
            'mn_name' => 'Access',
            'mn_code' => 'access',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn11'),
            'mn_order' => 2,
            'mn_icon' => 'fa fa-tasks',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn14'),
            'mn_name' => 'Master',
            'mn_code' => 'master',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn11'),
            'mn_order' => 3,
            'mn_icon' => 'fa fa-tasks',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn15'),
            'mn_name' => 'Document',
            'mn_code' => 'document',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn11'),
            'mn_order' => 4,
            'mn_icon' => 'fa fa-tasks',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('menu')->insert([
            'mn_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn16'),
            'mn_name' => 'Page',
            'mn_code' => 'page',
            'mn_parent' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'mn11'),
            'mn_order' => 1,
            'mn_icon' => 'fa fa-tasks',
            'mn_active' => 'Y',
            'mn_created_on' => date('Y-m-d H:i:s'),
            'mn_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
    }
}

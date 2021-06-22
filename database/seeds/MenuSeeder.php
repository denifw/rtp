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
        DB::table('menu')->insert(['mn_id' => 'a1f1758d-c9ba-33b7-8604-4edc3db4feca', 'mn_name' => 'Root', 'mn_code' => 'root', 'mn_order' => 1, 'mn_icon' => 'fa fa-sitemap', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'e877f91c-1059-3d96-8eaa-45969ecdd0c9', 'mn_name' => 'Statistic', 'mn_code' => 'statistic', 'mn_order' => 2, 'mn_icon' => 'fa fa-bar-chart', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => '1dcebba1-e8ec-3fe7-817b-d30c8db406db', 'mn_name' => 'Operation', 'mn_code' => 'operation', 'mn_parent' => 'e877f91c-1059-3d96-8eaa-45969ecdd0c9', 'mn_order' => 1, 'mn_icon' => 'fa fa-institution', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => '7f2cffc0-4759-325d-b324-7b61c6bf9a66', 'mn_name' => 'Finance', 'mn_code' => 'finance', 'mn_parent' => 'e877f91c-1059-3d96-8eaa-45969ecdd0c9', 'mn_order' => 2, 'mn_icon' => 'fa fa-money', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'a6170047-30b2-33cc-a997-c3d4449e2e8f', 'mn_name' => 'CRM', 'mn_code' => 'crm', 'mn_order' => 3, 'mn_icon' => 'fa fa-sitemap', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => '02cffd3a-33ea-3bdf-be3f-80c0a7a14a53', 'mn_name' => 'Operation', 'mn_code' => 'operation', 'mn_order' => 4, 'mn_icon' => 'fa fa-institution', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => '8280e327-d322-35bd-a479-63d37c056610', 'mn_name' => 'Master', 'mn_code' => 'master', 'mn_order' => 6, 'mn_icon' => 'fa fa-book', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'c388342a-7bb3-3318-b7a9-80fe0a0920df', 'mn_name' => 'Finance', 'mn_code' => 'finance', 'mn_parent' => '8280e327-d322-35bd-a479-63d37c056610', 'mn_order' => 2, 'mn_icon' => 'fa fa-money', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => '156ee4e0-1598-3f98-b35a-c6a12671129f', 'mn_name' => 'Administration', 'mn_code' => 'administration', 'mn_order' => 5, 'mn_icon' => 'fa fa-briefcase', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'eeb0e4de-daa9-382f-9c46-93482555fc7d', 'mn_name' => 'System', 'mn_code' => 'system', 'mn_order' => 100, 'mn_icon' => 'fa fa-cogs', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'e22f7321-223d-39dc-b737-38117edcc177', 'mn_name' => 'Master', 'mn_code' => 'master', 'mn_parent' => 'eeb0e4de-daa9-382f-9c46-93482555fc7d', 'mn_order' => 1, 'mn_icon' => 'fa fa-book', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => '69fd2e2e-7690-36e6-9457-fd2b499d2c9c', 'mn_name' => 'Access', 'mn_code' => 'access', 'mn_parent' => 'eeb0e4de-daa9-382f-9c46-93482555fc7d', 'mn_order' => 2, 'mn_icon' => 'fa fa-users', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'f747ef1d-99e6-36de-8299-dcc70c492582', 'mn_name' => 'Document', 'mn_code' => 'document', 'mn_parent' => 'eeb0e4de-daa9-382f-9c46-93482555fc7d', 'mn_order' => 3, 'mn_icon' => 'fa fa-file', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'd873a7c3-5a3c-3dde-a967-3cb1dd22317c', 'mn_name' => 'Page', 'mn_code' => 'page', 'mn_parent' => 'eeb0e4de-daa9-382f-9c46-93482555fc7d', 'mn_order' => 4, 'mn_icon' => 'fa fa-globe', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('menu')->insert(['mn_id' => 'eef8f1f1-1e9d-36a3-805b-489b9aeb4301', 'mn_name' => 'Employee', 'mn_code' => 'employee', 'mn_parent' => '8280e327-d322-35bd-a479-63d37c056610', 'mn_order' => 1, 'mn_icon' => 'fa fa-tasks', 'mn_active' => 'Y', 'mn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'mn_created_on' => date('Y-m-d H:i:s')]);
    }
}

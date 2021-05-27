<?php

use Illuminate\Database\Seeder;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_group_api_access')->truncate();
        DB::table('user_group_detail')->truncate();
        DB::table('user_group_right')->truncate();
        DB::table('user_group_page')->truncate();
        DB::table('user_group')->truncate();
        DB::table('page_right')->truncate();
        DB::table('page')->truncate();
        DB::table('menu')->truncate();
        $this->call([
            MenuSeeder::class,
            PageSeeder::class,
            PageRightSeeder::class,
            UserGroupSeeder::class,
            UserGroupPageSeeder::class,
            UserGroupRightSeeder::class,
            UserGroupDetailSeeder::class,
            UserGroupApiSeeder::class,
        ]);
//        $this->call([
//            SerialNumberSeeder::class,
//            UserGroupPageSeeder::class,
//            UserGroupRightSeeder::class,
//        ]);
//        # Cash Account Seeder
//        DB::table('cash_account')->insert(['cac_ss_id' => 2, 'cac_code' => 'B0001', 'cac_ar' => 'Y', 'cac_ap' => 'N', 'cac_master' => 'Y', 'cac_active' => 'Y', 'cac_created_on' => date('Y-m-d H:i:s'), 'cac_created_by' => 1]);
//        DB::table('cash_account')->insert(['cac_ss_id' => 2, 'cac_code' => 'B0002', 'cac_ar' => 'N', 'cac_ap' => 'Y', 'cac_master' => 'Y', 'cac_active' => 'Y', 'cac_created_on' => date('Y-m-d H:i:s'), 'cac_created_by' => 1]);
//        DB::table('cash_account')->insert(['cac_ss_id' => 2, 'cac_code' => 'B0003', 'cac_srv_id' => 3, 'cac_us_id' => 2, 'cac_limit' => 10000000, 'cac_ar' => 'N', 'cac_ap' => 'Y', 'cac_master' => 'N', 'cac_active' => 'Y', 'cac_created_on' => date('Y-m-d H:i:s'), 'cac_created_by' => 1]);
    }
}

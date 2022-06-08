<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(['us_id' => 'c1d5f0bf-4a0f-39ba-a542-8dd7d55d7602', 'us_name' => 'Deni Firdaus Waruwu', 'us_username' => 'deni.firdaus.w@gmail.com', 'us_password' => bcrypt('localhost'), 'us_system' => 'N', 'us_lg_id' => 'c5b453c7-a319-36a2-b854-1789c930733d', 'us_menu_style' => 'nav-md', 'us_confirm' => 'Y', 'us_active' => 'Y', 'us_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'us_created_on' => date('Y-m-d H:i:s')]);
        DB::table('users')->insert(['us_id' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'us_name' => 'System Administrator', 'us_username' => 'system@qomteq.com', 'us_password' => bcrypt('localhost'), 'us_system' => 'Y', 'us_lg_id' => 'c5b453c7-a319-36a2-b854-1789c930733d', 'us_menu_style' => 'nav-md', 'us_confirm' => 'Y', 'us_active' => 'Y', 'us_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'us_created_on' => date('Y-m-d H:i:s')]);
    }
}

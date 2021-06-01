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
        DB::table('users')->insert([
            'us_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1'),
            'us_name' => 'System Administrator',
            'us_username' => 'system@spada-informatika.com',
            'us_password' => bcrypt('localhost'),
            'us_lg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'lg2'),
            'us_menu_style' => 'nav-md',
            'us_system' => 'Y',
            'us_confirm' => 'Y',
            'us_active' => 'Y',
            'us_created_on' => date('Y-m-d H:i:s'),
            'us_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1'),
        ]);
        DB::table('users')->insert([
            'us_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us2'),
            'us_name' => 'Deni Firdaus Waruwu',
            'us_username' => 'deni.firdaus.w@gmail.com',
            'us_password' => bcrypt('localhost'),
            'us_lg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'lg2'),
            'us_menu_style' => 'nav-md',
            'us_system' => 'Y',
            'us_confirm' => 'Y',
            'us_active' => 'Y',
            'us_created_on' => date('Y-m-d H:i:s'),
            'us_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1'),
        ]);
    }
}

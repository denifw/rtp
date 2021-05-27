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
        $uidKey = microtime() . 'us10';
        DB::table('users')->insert([
            'us_name' => 'Ega',
            'us_username' => 'ega@mbs-logistik.com',
            'us_password' => bcrypt('MBS2021'),
            'us_allow_mail' => 'N',
            'us_lg_id' => 1,
            'us_system' => 'N',
            'us_confirm' => 'Y',
            'us_active' => 'Y',
            'us_created_on' => date('Y-m-d H:i:s'),
            'us_created_by' => 1,
            'us_uid' => Uuid::uuid3(Uuid::NAMESPACE_URL, $uidKey)
        ]);
    }
}

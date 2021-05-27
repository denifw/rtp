<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class UserMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_mapping')->insert([
                'ump_us_id' => 142,
                'ump_ss_id' => 2,
                'ump_rel_id' => 2,
                'ump_cp_id' => 922,
                'ump_confirm' => 'Y',
                'ump_default' => 'Y',
                'ump_active' => 'Y',
                'ump_uid' => Uuid::uuid3(Uuid::NAMESPACE_URL, microtime() . 'ump10'),
                'ump_created_on' => date('Y-m-d H:i:s'),
                'ump_created_by' => 1]
        );
        DB::table('user_mapping')->insert([
                'ump_us_id' => 142,
                'ump_ss_id' => 3,
                'ump_rel_id' => 3,
                'ump_cp_id' => 923,
                'ump_confirm' => 'Y',
                'ump_default' => 'Y',
                'ump_active' => 'Y',
                'ump_uid' => Uuid::uuid3(Uuid::NAMESPACE_URL, microtime() . 'ump11'),
                'ump_created_on' => date('Y-m-d H:i:s'),
                'ump_created_by' => 1]
        );
    }
}

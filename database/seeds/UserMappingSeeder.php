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
                'ump_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'ump1'),
                'ump_us_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us2'),
                'ump_ss_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'ss2'),
                'ump_rel_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'rel2'),
                'ump_cp_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'cp2'),
                'ump_confirm' => 'Y',
                'ump_default' => 'Y',
                'ump_active' => 'Y',
                'ump_created_on' => date('Y-m-d H:i:s'),
                'ump_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]
        );
    }
}

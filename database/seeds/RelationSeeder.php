<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class RelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('relation')->insert([
            'rel_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'rel1'),
            'rel_ss_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'ss1'),
            'rel_name' => 'System Administrator',
            'rel_number' => 'REL-210100001',
            'rel_short_name' => 'SYA',
            'rel_of_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'of1'),
            'rel_cp_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'cp1'),
            'rel_active' => 'Y',
            'rel_created_on' => date('Y-m-d H:i:s'),
            'rel_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
    }
}

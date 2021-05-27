<?php

use Illuminate\Database\Seeder;

class RelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('relation')->insert(['rel_ss_id' => 1, 'rel_name' => 'PT Spada Media Informatika', 'rel_number' => 'REL-190000000001', 'rel_short_name' => 'SMI', 'rel_website' => '', 'rel_email' => '', 'rel_phone' => '', 'rel_vat' => '', 'rel_remark' => null, 'rel_owner' => 'Y', 'rel_active' => 'Y', 'rel_created_on' => date('Y-m-d H:i:s'), 'rel_created_by' => 1]);
    }
}

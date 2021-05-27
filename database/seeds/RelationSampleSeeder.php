<?php

use Illuminate\Database\Seeder;

class RelationSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('relation')->insert(['rel_ss_id' => 2, 'rel_name' => 'PT Bukitmega Masabadi', 'rel_number' => 'REL-190000000002', 'rel_short_name' => 'BMA', 'rel_website' => 'http://bukitmega.com/', 'rel_email' => 'info@bukitmega.com', 'rel_phone' => '(021) 26530007', 'rel_vat' => '', 'rel_remark' => 'null', 'rel_owner' => 'N', 'rel_active' => 'Y', 'rel_created_on' => date('Y-m-d H:i:s'), 'rel_created_by' => 1]);
        DB::table('relation')->insert(['rel_ss_id' => 2, 'rel_name' => 'PT Indonesia Seia', 'rel_number' => 'REL-190000000003', 'rel_short_name' => 'ISA', 'rel_website' => 'inseia.com', 'rel_email' => 'info@inseia.com', 'rel_phone' => '(021) 29569574', 'rel_vat' => '', 'rel_remark' => 'null', 'rel_owner' => 'N', 'rel_active' => 'Y', 'rel_created_on' => date('Y-m-d H:i:s'), 'rel_created_by' => 1]);
    }
}

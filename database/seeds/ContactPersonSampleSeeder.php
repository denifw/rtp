<?php

use Illuminate\Database\Seeder;

class ContactPersonSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contact_person')->insert(['cp_number' => 'CP-190000000002', 'cp_name' => 'Sutrisno', 'cp_email' => 'sutrisno@bukitmega.com', 'cp_phone' => '0812734773822', 'cp_of_id' => 3, 'cp_office_manager' => 'Y', 'cp_active' => 'Y', 'cp_created_on' => date('Y-m-d H:i:s'), 'cp_created_by' => 1]);
        DB::table('contact_person')->insert(['cp_number' => 'CP-190000000003', 'cp_name' => 'Paijo', 'cp_email' => 'paijo@inseia.com', 'cp_phone' => '08397637483', 'cp_of_id' => 4, 'cp_office_manager' => 'Y', 'cp_active' => 'Y', 'cp_created_on' => date('Y-m-d H:i:s'), 'cp_created_by' => 1]);
        DB::table('contact_person')->insert(['cp_number' => 'CP-190000000004', 'cp_name' => 'Jhonnys', 'cp_email' => 'jhonny@mbs-logistik.com', 'cp_phone' => '', 'cp_of_id' => 5, 'cp_office_manager' => 'Y', 'cp_active' => 'Y', 'cp_created_on' => date('Y-m-d H:i:s'), 'cp_created_by' => 1]);
    }
}

<?php

use Illuminate\Database\Seeder;

class ContactPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contact_person')->insert(['cp_number' => 'CP-2100160', 'cp_name' => 'Ega', 'cp_email' => 'ega@mbs-logistik.com', 'cp_of_id' => 2, 'cp_office_manager' => 'N', 'cp_active' => 'Y', 'cp_salutation_id' => 7, 'cp_dpt_id' => 5, 'cp_uid' => 'c93092e3-9384-30ad-a143-dd1e032ea57b', 'cp_created_on' => date('Y-m-d H:i:s'), 'cp_created_by' => 1]);
        DB::table('contact_person')->insert(['cp_number' => 'CP-2100002', 'cp_name' => 'Ega', 'cp_email' => 'ega@mbs-logistik.com', 'cp_of_id' => 3, 'cp_office_manager' => 'N', 'cp_active' => 'Y', 'cp_salutation_id' => 7, 'cp_uid' => 'f292e1c0-9f1b-33d5-94a4-f6b666f89b20', 'cp_created_on' => date('Y-m-d H:i:s'), 'cp_created_by' => 1]);
    }
}

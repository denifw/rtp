<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class ContactPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contact_person')->insert([
            'cp_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'cp1'),
            'cp_number' => 'CP-210100001',
            'cp_name' => 'System Admin',
            'cp_email' => 'system@spada-informatika.com',
            'cp_of_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'of1'),
            'cp_active' => 'Y',
            'cp_created_on' => date('Y-m-d H:i:s'),
            'cp_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('office')->insert([
            'of_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'of1'),
            'of_rel_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'rel1'),
            'of_name' => 'SMI',
            'of_invoice' => 'Y',
            'of_cp_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'cp1'),
            'of_address' => 'Jalan Kramat Jaya No. 48, Tugu Utara',
            'of_active' => 'Y',
            'of_created_on' => date('Y-m-d H:i:s'),
            'of_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')]);
    }
}

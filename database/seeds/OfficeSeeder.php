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
        DB::table('office')->insert(['of_id' => '4816ddf5-ae84-3594-9de1-b16dd8df96ce', 'of_rel_id' => 'f545a902-b73b-3658-a752-db5ece6cdb08', 'of_name' => 'QOMTEQ', 'of_invoice' => 'Y', 'of_address' => 'Jalan Kramat Jaya No. 48, Tugu Utara', 'of_cp_id' => '1d421d8c-bffe-3fb8-b7f6-8b4ee79f740a', 'of_active' => 'Y', 'of_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'of_created_on' => date('Y-m-d H:i:s')]);
        DB::table('office')->insert(['of_id' => '72a86af8-64b6-374d-8ada-de8664243a4e', 'of_rel_id' => '07fb49e6-8d87-332e-a3e2-9fec83b597d4', 'of_name' => 'GLOIPID', 'of_invoice' => 'Y', 'of_address' => 'Jalan Kramat Jaya No. 48, Tugu Utara', 'of_cp_id' => '67d3d4e8-6872-345a-ac9b-2268db54f193', 'of_active' => 'Y', 'of_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'of_created_on' => date('Y-m-d H:i:s')]);
    }
}

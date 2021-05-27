<?php

use Illuminate\Database\Seeder;

class ContainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('container')->insert(['ct_name' => '20 FT', 'ct_length' => 6.3, 'ct_width' => 2.35, 'ct_height' => 2.39, 'ct_volume' => 33, 'ct_max_weight' => 16000, 'ct_active' => 'Y', 'ct_created_on' => date('Y-m-d H:i:s'), 'ct_created_by' => 1]);
        DB::table('container')->insert(['ct_name' => '40 FT', 'ct_length' => 12, 'ct_width' => 2.35, 'ct_height' => 2.39, 'ct_volume' => 64, 'ct_max_weight' => 25000, 'ct_active' => 'Y', 'ct_created_on' => date('Y-m-d H:i:s'), 'ct_created_by' => 1]);
    }
}

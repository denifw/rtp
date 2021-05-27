<?php

use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('brand')->insert(['br_ss_id' => 2, 'br_name' => 'Brand A', 'br_active' => 'Y', 'br_created_on' => date('Y-m-d H:i:s'), 'br_created_by' => 1]);
        DB::table('brand')->insert(['br_ss_id' => 2, 'br_name' => 'Brand B', 'br_active' => 'Y', 'br_created_on' => date('Y-m-d H:i:s'), 'br_created_by' => 1]);
    }
}

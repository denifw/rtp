<?php

use Illuminate\Database\Seeder;

class OwnershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ownership_type')->insert(['owt_name' => 'Company Asset', 'owt_active' => 'Y', 'owt_created_on' => date('Y-m-d H:i:s'), 'owt_created_by' => 1]);
        DB::table('ownership_type')->insert(['owt_name' => 'Hired', 'owt_active' => 'Y', 'owt_created_on' => date('Y-m-d H:i:s'), 'owt_created_by' => 1]);

    }
}

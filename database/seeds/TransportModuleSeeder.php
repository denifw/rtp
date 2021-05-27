<?php

use Illuminate\Database\Seeder;

class TransportModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('transport_module')->insert(['tm_name' => 'Road', 'tm_active' => 'Y', 'tm_created_on' => date('Y-m-d H:i:s'), 'tm_created_by' => 1]);
        DB::table('transport_module')->insert(['tm_name' => 'Rail', 'tm_active' => 'Y', 'tm_created_on' => date('Y-m-d H:i:s'), 'tm_created_by' => 1]);
        DB::table('transport_module')->insert(['tm_name' => 'Sea', 'tm_active' => 'Y', 'tm_created_on' => date('Y-m-d H:i:s'), 'tm_created_by' => 1]);
        DB::table('transport_module')->insert(['tm_name' => 'Air', 'tm_active' => 'Y', 'tm_created_on' => date('Y-m-d H:i:s'), 'tm_created_by' => 1]);
    }
}

<?php

use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service')
            ->where('srv_id', 1)
            ->update(['srv_image' => 'warehouse.png']);
        DB::table('service')
            ->where('srv_id', 2)
            ->update(['srv_image' => 'inklaring.png']);
        DB::table('service')
            ->where('srv_id', 3)
            ->update([
                'srv_name' => 'Delivery',
                'srv_code' => 'delivery',
                'srv_image' => 'trucking.png',
            ]);
    }
}

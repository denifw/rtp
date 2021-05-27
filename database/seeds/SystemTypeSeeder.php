<?php

use Illuminate\Database\Seeder;

class SystemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('system_type')->insert(['sty_group' => 'dashboardmodule', 'sty_name' => 'Warehouse', 'sty_active' => 'Y', 'sty_order' => 1, 'sty_uid' => 'af1376c8-0929-3bdb-9fe3-bd9fa7bb858c', 'sty_created_on' => date('Y-m-d H:i:s'), 'sty_created_by' => 1]);
        DB::table('system_type')->insert(['sty_group' => 'dashboardmodule', 'sty_name' => 'Inklaring', 'sty_active' => 'Y', 'sty_order' => 2, 'sty_uid' => '669b4da3-eda3-3e92-9aa5-b50eb5606cd6', 'sty_created_on' => date('Y-m-d H:i:s'), 'sty_created_by' => 1]);
        DB::table('system_type')->insert(['sty_group' => 'dashboardmodule', 'sty_name' => 'Delivery', 'sty_active' => 'Y', 'sty_order' => 3, 'sty_uid' => '05834da3-c80a-3625-915c-b66738961e5e', 'sty_created_on' => date('Y-m-d H:i:s'), 'sty_created_by' => 1]);
        DB::table('system_type')->insert(['sty_group' => 'dashboardmodule', 'sty_name' => 'General', 'sty_active' => 'Y', 'sty_order' => 4, 'sty_uid' => '3fb19284-2726-4511-82ca-922f075b1d28', 'sty_created_on' => date('Y-m-d H:i:s'), 'sty_created_by' => 1]);
    }
}

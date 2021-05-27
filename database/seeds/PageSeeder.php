<?php

use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('page')->where('pg_id', 256)->update([
            'pg_title' => 'COGS Delivery',
            'pg_description' => 'COGS Delivery',
            'pg_route' => 'prcPrcDl',
        ]);
        DB::table('page')->where('pg_id', 255)->update([
            'pg_title' => 'COGS Delivery',
            'pg_description' => 'COGS Delivery',
            'pg_route' => 'prcPrcDl',
        ]);
        DB::table('page')->where('pg_id', 244)->update([
            'pg_title' => 'Price Delivery',
            'pg_description' => 'Price Delivery',
            'pg_route' => 'prcSlsDl',
        ]);
        DB::table('page')->where('pg_id', 261)->update([
            'pg_title' => 'Price Delivery',
            'pg_description' => 'Price Delivery',
            'pg_route' => 'prcSlsDl',
        ]);

        # Cash And Bank
        DB::table('page')->where('pg_id', 167)->update([
            'pg_title' => 'Bank Account',
            'pg_description' => 'List Bank Account',
            'pg_route' => 'ba',
            'pg_order' => 6,
        ]);
        DB::table('page')->where('pg_id', 168)->update([
            'pg_title' => 'Bank Account',
            'pg_description' => 'Detail Bank Account',
            'pg_route' => 'ba',
        ]);
        DB::table('page')->where('pg_id', 163)->update([
            'pg_title' => 'Bank Account',
            'pg_description' => 'Detail Bank Account',
            'pg_route' => 'ba',
        ]);
        DB::table('page')->where('pg_id', 1)->update([
            'pg_title' => 'Request & Return',
            'pg_description' => 'Request & Return',
            'pg_route' => 'topUp',
            'pg_order' => 3,
        ]);
        DB::table('page')->where('pg_id', 111)->update([
            'pg_title' => 'Request & Return',
            'pg_description' => 'Request & Return',
            'pg_route' => 'topUp',
        ]);
        DB::table('page')->where('pg_id', 61)->update([
            'pg_title' => 'Cash Payment',
            'pg_description' => 'List Cash Payment',
            'pg_route' => 'ca',
            'pg_order' => 2,
        ]);
        DB::table('page')->where('pg_id', 62)->update([
            'pg_title' => 'Cash Payment',
            'pg_description' => 'Detail Cash Payment',
            'pg_route' => 'ca',
        ]);
        DB::table('page')->where('pg_id', 156)->update([
            'pg_title' => 'Bank Mutation',
            'pg_description' => 'Bank Mutation',
            'pg_route' => 'baMutation',
            'pg_mn_id' => 30,
            'pg_order' => 3,
        ]);

        DB::table('page')->insert(['pg_title' => 'E-Card', 'pg_description' => 'Electronic Card', 'pg_route' => 'ea', 'pg_mn_id' => 29, 'pg_pc_id' => 2, 'pg_icon' => 'fa fa-tasks', 'pg_order' => 7, 'pg_default' => 'Y', 'pg_system' => 'N', 'pg_active' => 'Y', 'pg_uid' => '62468c92-6f1c-37e5-826c-a0f33f5320a7', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => 1]);
        DB::table('page')->insert(['pg_title' => 'E-Card', 'pg_description' => 'Electronic Card', 'pg_route' => 'ea', 'pg_pc_id' => 3, 'pg_default' => 'Y', 'pg_system' => 'N', 'pg_active' => 'Y', 'pg_uid' => '0b8ff3a8-f88e-3d7e-890a-11889a7825cb', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => 1]);
        DB::table('page')->insert(['pg_title' => 'E-Card Mutation', 'pg_description' => 'E-Card Mutation', 'pg_route' => 'eaMutation', 'pg_mn_id' => 30, 'pg_pc_id' => 4, 'pg_icon' => 'fa fa-tasks', 'pg_order' => 5, 'pg_default' => 'Y', 'pg_system' => 'N', 'pg_active' => 'Y', 'pg_uid' => 'cf3d055c-877b-3868-a89d-e87763beb58b', 'pg_created_on' => date('Y-m-d H:i:s'), 'pg_created_by' => 1]);
        # Disable Page
        DB::table('page')->where('pg_id', 127)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 128)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 130)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 131)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 133)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 134)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 141)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 142)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 143)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 144)->update([
            'pg_active' => 'N',
        ]);
        DB::table('page')->where('pg_id', 147)->update([
            'pg_active' => 'N',
        ]);
    }
}

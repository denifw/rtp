<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SerialNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('serial_number')->where('sn_id', 24)->update([
            'sn_prefix' => 'BT'
        ]);
        DB::table('serial_number')->insert(['sn_sc_id' => 8, 'sn_ss_id' => 1, 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'BT', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_uid' => '186e6020-9b00-3f37-a359-4dcbb94f0be5', 'sn_created_on' => date('Y-m-d H:i:s'), 'sn_created_by' => 1]);
        DB::table('serial_number')->insert(['sn_sc_id' => 8, 'sn_ss_id' => 3, 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'BT', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_uid' => 'e4d6c801-46f0-3bdb-9fd6-7bba1a12ca7e', 'sn_created_on' => date('Y-m-d H:i:s'), 'sn_created_by' => 1]);
        DB::table('serial_number')->insert(['sn_sc_id' => 8, 'sn_ss_id' => 5, 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'BT', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_uid' => 'b2d9fd5c-ccfb-3e3d-a958-a7977b2fdd10', 'sn_created_on' => date('Y-m-d H:i:s'), 'sn_created_by' => 1]);
        DB::table('serial_number')->insert(['sn_sc_id' => 8, 'sn_ss_id' => 7, 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'BT', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_uid' => '0915586b-85ed-3687-ab70-d75da340e75f', 'sn_created_on' => date('Y-m-d H:i:s'), 'sn_created_by' => 1]);
        DB::table('serial_number')->insert(['sn_sc_id' => 8, 'sn_ss_id' => 6, 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'BT', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_uid' => '9b5d1e2b-c700-344a-a7ce-817d2dd944c3', 'sn_created_on' => date('Y-m-d H:i:s'), 'sn_created_by' => 1]);
        DB::table('serial_number')->insert(['sn_sc_id' => 8, 'sn_ss_id' => 4, 'sn_relation' => 'N', 'sn_format' => 'A', 'sn_separator' => '-', 'sn_prefix' => 'BT', 'sn_yearly' => 'Y', 'sn_monthly' => 'Y', 'sn_length' => 3, 'sn_increment' => 1, 'sn_active' => 'Y', 'sn_uid' => '7a7aa7ea-deac-3f16-a2d5-5070a405ec58', 'sn_created_on' => date('Y-m-d H:i:s'), 'sn_created_by' => 1]);
    }
}

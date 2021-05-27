<?php

use Illuminate\Database\Seeder;

class JobWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('job_warehouse')->insert(['jow_jo_id' => 1, 'jow_wh_id' => 1, 'jow_eta_date' => '2019-05-15', 'jow_eta_time' => '09:08:00', 'jow_rel_id' => 3, 'jow_of_id' => 3, 'jow_cp_id' => 3, 'jow_created_on' => date('Y-m-d H:i:s'), 'jow_created_by' => 1]);
    }
}

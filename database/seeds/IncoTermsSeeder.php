<?php

use Illuminate\Database\Seeder;

class IncoTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('inco_terms')->insert(['ict_code' => 'DTD', 'ict_name' => 'Door to Door', 'ict_pol' => 'N', 'ict_pod' => 'N', 'ict_load' => 'Y', 'ict_unload' => 'Y', 'ict_uid' => 'e0ab2280-77b4-3ccd-84e6-bfff3cba03a5', 'ict_created_on' => date('Y-m-d H:i:s'), 'ict_created_by' => 1]);
        DB::table('inco_terms')->insert(['ict_code' => 'DTP', 'ict_name' => 'Door to Port', 'ict_pol' => 'Y', 'ict_pod' => 'N', 'ict_load' => 'Y', 'ict_unload' => 'N', 'ict_uid' => '9daa2571-f4f0-35ac-b221-9ef00fc8f4f7', 'ict_created_on' => date('Y-m-d H:i:s'), 'ict_created_by' => 1]);
        DB::table('inco_terms')->insert(['ict_code' => 'PTD', 'ict_name' => 'Port to Door', 'ict_pol' => 'N', 'ict_pod' => 'Y', 'ict_load' => 'N', 'ict_unload' => 'Y', 'ict_uid' => 'ba816a1c-23d5-3a7c-b9e8-d4f1b9262261', 'ict_created_on' => date('Y-m-d H:i:s'), 'ict_created_by' => 1]);
        DB::table('inco_terms')->insert(['ict_code' => 'PTP', 'ict_name' => 'Port to Port', 'ict_pol' => 'Y', 'ict_pod' => 'Y', 'ict_load' => 'N', 'ict_unload' => 'N', 'ict_uid' => 'f711fbb7-2104-3a9c-b935-1bd922dd73c2', 'ict_created_on' => date('Y-m-d H:i:s'), 'ict_created_by' => 1]);
    }
}

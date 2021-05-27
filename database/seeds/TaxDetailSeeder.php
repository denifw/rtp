<?php

use Illuminate\Database\Seeder;

class TaxDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tax_detail')->insert(['td_tax_id' => 1, 'td_name' => 'PPH 23', 'td_active' => 'Y', 'td_percent' => -2, 'td_created_on' => date('Y-m-d H:i:s'), 'td_created_by' => 1]);
        DB::table('tax_detail')->insert(['td_tax_id' => 2, 'td_name' => 'PPN 10%', 'td_active' => 'Y', 'td_percent' => 10, 'td_created_on' => date('Y-m-d H:i:s'), 'td_created_by' => 1]);
        DB::table('tax_detail')->insert(['td_tax_id' => 3, 'td_name' => 'PPH 10%', 'td_active' => 'Y', 'td_percent' => 10, 'td_created_on' => date('Y-m-d H:i:s'), 'td_created_by' => 1]);
        DB::table('tax_detail')->insert(['td_tax_id' => 3, 'td_name' => 'PPH 23', 'td_active' => 'Y', 'td_percent' => -2, 'td_created_on' => date('Y-m-d H:i:s'), 'td_created_by' => 1]);
    }
}

<?php

use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tax')->insert(['tax_ss_id' => 2, 'tax_name' => 'PPH', 'tax_active' => 'Y', 'tax_created_on' => date('Y-m-d H:i:s'), 'tax_created_by' => 1]);
        DB::table('tax')->insert(['tax_ss_id' => 2, 'tax_name' => 'PPN', 'tax_active' => 'Y', 'tax_created_on' => date('Y-m-d H:i:s'), 'tax_created_by' => 1]);
        DB::table('tax')->insert(['tax_ss_id' => 2, 'tax_name' => 'PPH + PPN', 'tax_active' => 'Y', 'tax_created_on' => date('Y-m-d H:i:s'), 'tax_created_by' => 1]);
    }
}

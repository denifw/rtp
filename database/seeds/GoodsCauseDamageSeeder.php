<?php

use Illuminate\Database\Seeder;

class GoodsCauseDamageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('goods_cause_damage')->insert(['gcd_ss_id' => 2, 'gcd_code' => 'GC0001', 'gcd_description' => 'Damage before unload.', 'gcd_active' => 'Y', 'gcd_created_on' => date('Y-m-d H:i:s'), 'gcd_created_by' => 1]);
        DB::table('goods_cause_damage')->insert(['gcd_ss_id' => 2, 'gcd_code' => 'GC0002', 'gcd_description' => 'Damage when unload goods.', 'gcd_active' => 'Y', 'gcd_created_on' => date('Y-m-d H:i:s'), 'gcd_created_by' => 1]);
    }
}

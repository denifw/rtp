<?php

use Illuminate\Database\Seeder;

class GoodsDamageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('goods_damage_type')->insert(['gdt_ss_id' => 2, 'gdt_code' => 'DM0001', 'gdt_description' => 'The wrap is torn', 'gdt_active' => 'Y', 'gdt_created_on' => date('Y-m-d H:i:s'), 'gdt_created_by' => 1]);
        DB::table('goods_damage_type')->insert(['gdt_ss_id' => 2, 'gdt_code' => 'DM0002', 'gdt_description' => 'Goods Wet', 'gdt_active' => 'Y', 'gdt_created_on' => date('Y-m-d H:i:s'), 'gdt_created_by' => 1]);
    }
}

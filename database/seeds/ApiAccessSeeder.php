<?php

use Illuminate\Database\Seeder;

class ApiAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('api_access')->insert(['aa_name' => 'AllowSeeAllJob', 'aa_description' => 'Allow user to access all jobs.', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'AllowSeeJobAsRelation', 'aa_description' => 'Allow user to only jobs related to the relation user.', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'AllowUpdateArrivalOfTruck', 'aa_description' => 'Allow user to update the arrival of truck.', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'AllowUpdateJobAction', 'aa_description' => 'Allow user to update job action.', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'GoodsDamageRequiredWeight', 'aa_description' => 'Registration Goods Damage Required weight of goods.', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'OutboundAutoLoadingQuantity', 'aa_description' => 'Picking quantity equals to Loading quantity', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'AllowEmptyTruckNumberOutbound', 'aa_description' => 'Allow Empty Truck Number Outbound', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'AllowUpdateLotNumberInbound', 'aa_description' => 'Allow Update Lot Number Inbound Goods', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);
        DB::table('api_access')->insert(['aa_name' => 'AllowScannerSystem', 'aa_description' => 'Allow Scanner System', 'aa_default' => 'N', 'aa_active' => 'Y', 'aa_created_on' => date('Y-m-d H:i:s'), 'aa_created_by' => 1]);

    }
}

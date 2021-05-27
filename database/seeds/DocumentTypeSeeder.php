<?php

use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_type')
            ->where('dct_id', 64)
            ->update([
                'dct_code' => 'cpreceipt',
                'dct_description' => 'Payment Receipt'
            ]);
        DB::table('document_type')
            ->where('dct_id', 65)
            ->update([
                'dct_code' => 'cpsettlement',
                'dct_description' => 'Settlement Confirmation'
            ]);
        DB::table('document_type')->insert(['dct_dcg_id' => 11, 'dct_code' => 'image', 'dct_description' => 'Image', 'dct_master' => 'Y', 'dct_active' => 'Y', 'dct_uid' => '8f0894e1-02dc-3dba-a773-3fc2d1442bc2', 'dct_created_on' => date('Y-m-d H:i:s'), 'dct_created_by' => 1]);
        DB::table('document_type')->insert(['dct_dcg_id' => 16, 'dct_code' => 'topupreceipt', 'dct_description' => 'Top Up Receipt', 'dct_master' => 'Y', 'dct_active' => 'Y', 'dct_uid' => 'bc667751-0ae2-33f0-b7c2-72e250e955dc', 'dct_created_on' => date('Y-m-d H:i:s'), 'dct_created_by' => 1]);
        DB::table('document_type')->insert(['dct_dcg_id' => 7, 'dct_code' => 'cpreceive', 'dct_description' => 'Receive Confirmation', 'dct_master' => 'Y', 'dct_active' => 'Y', 'dct_uid' => '2eed8513-bce9-3b5f-a1e0-32db7351e9b8', 'dct_created_on' => date('Y-m-d H:i:s'), 'dct_created_by' => 1]);
    }
}

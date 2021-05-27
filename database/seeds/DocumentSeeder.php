<?php

use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document')->insert(['doc_dct_id' => 1, 'doc_ss_id' => 1, 'doc_group_reference' => 1, 'doc_type_reference' => null, 'doc_file_name' => 'spada1556082418.png', 'doc_file_size' => 20404, 'doc_file_type' => 'png', 'doc_public' => 'Y', 'doc_created_on' => date('Y-m-d H:i:s'), 'doc_created_by' => 1]);
    }
}

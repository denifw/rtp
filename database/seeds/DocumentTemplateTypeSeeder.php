<?php

use Illuminate\Database\Seeder;

class DocumentTemplateTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_template_type')
            ->where('dtt_id', 11)->update([
                'dtt_description' => 'Cash Payment Receive',
                'dtt_code' => 'cpreceive'
            ]);
        DB::table('document_template_type')
            ->where('dtt_id', 12)->update([
                'dtt_description' => 'Cash Payment Settlement',
                'dtt_code' => 'cpsettlement'
            ]);
    }
}

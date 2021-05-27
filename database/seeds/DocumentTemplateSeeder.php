<?php

use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_template')->where('dt_id', 19)->update([ 'dt_description' => 'Default Template', 'dt_path' => 'Finance/CashAndBank/CashPayment/CashReceive/DefaultTemplate', ]);
        DB::table('document_template')->where('dt_id', 20)->update([ 'dt_description' => 'Default Template', 'dt_path' => 'Finance/CashAndBank/CashPayment/CashSettlement/DefaultTemplate', ]);
    }
}

<?php

use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('languages')->insert([
            'lg_locale' => 'English',
            'lg_iso' => 'en',
            'lg_active' => 'Y',
            'lg_created_on' => date('Y-m-d H:i:s'),
            'lg_created_by' => 1
        ]);
        DB::table('languages')->insert([
            'lg_locale' => 'Bahasa Indonesia',
            'lg_iso' => 'id',
            'lg_active' => 'Y',
            'lg_created_on' => date('Y-m-d H:i:s'),
            'lg_created_by' => 1
        ]);
    }
}

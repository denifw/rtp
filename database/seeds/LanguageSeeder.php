<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

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
            'lg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'lg1'),
            'lg_locale' => 'English',
            'lg_iso' => 'en',
            'lg_active' => 'Y',
            'lg_created_on' => date('Y-m-d H:i:s'),
            'lg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
        DB::table('languages')->insert([
            'lg_id' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'lg2'),
            'lg_locale' => 'Bahasa Indonesia',
            'lg_iso' => 'id',
            'lg_active' => 'Y',
            'lg_created_on' => date('Y-m-d H:i:s'),
            'lg_created_by' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1')
        ]);
    }
}

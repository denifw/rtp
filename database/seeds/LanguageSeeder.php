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
        DB::table('languages')->insert(['lg_id' => 'c5b453c7-a319-36a2-b854-1789c930733d', 'lg_locale' => 'Bahasa Indonesia', 'lg_iso' => 'ID', 'lg_active' => 'Y', 'lg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'lg_created_on' => date('Y-m-d H:i:s')]);
        DB::table('languages')->insert(['lg_id' => 'cfbf040e-3daf-30ff-a351-aa129612d060', 'lg_locale' => 'English', 'lg_iso' => 'EN', 'lg_active' => 'Y', 'lg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'lg_created_on' => date('Y-m-d H:i:s')]);
    }
}

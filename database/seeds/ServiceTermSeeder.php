<?php

use Illuminate\Database\Seeder;

class ServiceTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service_term')
            ->where('srt_id', 6)
            ->update(['srt_pol' => 'N', 'srt_pod' => 'Y',]);
        DB::table('service_term')
            ->where('srt_id', 7)
            ->update(['srt_pol' => 'Y', 'srt_pod' => 'N',]);
        DB::table('service_term')
            ->where('srt_id', 8)
            ->update(['srt_pol' => 'N', 'srt_pod' => 'Y',]);
        DB::table('service_term')
            ->where('srt_id', 9)
            ->update(['srt_pol' => 'Y', 'srt_pod' => 'N',]);

    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterServiceTermRemoveModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_term', function (Blueprint $table) {
            $table->dropColumn('srt_trucking');
            $table->dropColumn('srt_forwarding');
            $table->dropColumn('srt_multi_trucking');
            $table->dropColumn('srt_warehouse');
            $table->dropColumn('srt_clearance');
            $table->dropColumn('srt_transhipment');
            $table->dropColumn('srt_courier');
            $table->char('srt_load', 1)->default('N')->nullable();
            $table->char('srt_unload', 1)->default('N')->nullable();
            $table->char('srt_pol', 1)->default('N')->nullable();
            $table->char('srt_pod', 1)->default('N')->nullable();
        });
        $query = 'SELECT srt_id FROM service_term';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('service_term')
                ->where('srt_id', $row->srt_id)
                ->update([
                    'srt_load' => 'N',
                    'srt_unload' => 'N',
                    'srt_pol' => 'N',
                    'srt_pod' => 'N',
                ]);
        }
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => ServiceTermSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_term', function (Blueprint $table) {
            $table->char('srt_trucking', 1)->default('N')->nullable();
            $table->char('srt_forwarding', 1)->default('N')->nullable();
            $table->char('srt_multi_trucking', 1)->default('N')->nullable();
            $table->char('srt_warehouse', 1)->default('N')->nullable();
            $table->char('srt_clearance', 1)->default('N')->nullable();
            $table->char('srt_transhipment', 1)->default('N')->nullable();
            $table->char('srt_courier', 1)->default('N')->nullable();
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteJobTruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_inklaring_release', function (Blueprint $table) {
            $table->dropForeign('tbl_jikr_joc_id_foreign');
            $table->dropColumn('jikr_joc_id');
        });
        Schema::dropIfExists('job_container');
        Schema::dropIfExists('job_trucking_detail');
        Schema::dropIfExists('job_trucking');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

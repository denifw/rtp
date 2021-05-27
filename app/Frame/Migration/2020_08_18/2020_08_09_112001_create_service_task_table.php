<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_task', function (Blueprint $table) {
            $table->bigIncrements('svt_id');
            $table->bigInteger('svt_ss_id')->unsigned();
            $table->foreign('svt_ss_id', 'tbl_svt_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('svt_name', 255);
            $table->char('svt_active', 1)->default('Y');
            $table->bigInteger('svt_created_by');
            $table->dateTime('svt_created_on');
            $table->bigInteger('svt_updated_by')->nullable();
            $table->dateTime('svt_updated_on')->nullable();
            $table->bigInteger('svt_deleted_by')->nullable();
            $table->dateTime('svt_deleted_on')->nullable();
            $table->unique(['svt_ss_id', 'svt_name'], 'tbl_svt_ss_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_task');
    }
}

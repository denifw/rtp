<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewal_type', function (Blueprint $table) {
            $table->bigIncrements('rnt_id');
            $table->bigInteger('rnt_ss_id')->nullable();
            $table->foreign('rnt_ss_id', 'tbl_rnt_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('rnt_name', 255);
            $table->char('rnt_active', 1)->default('Y');
            $table->bigInteger('rnt_created_by');
            $table->dateTime('rnt_created_on');
            $table->bigInteger('rnt_updated_by')->nullable();
            $table->dateTime('rnt_updated_on')->nullable();
            $table->bigInteger('rnt_deleted_by')->nullable();
            $table->dateTime('rnt_deleted_on')->nullable();
            $table->unique(['rnt_ss_id', 'rnt_name'], 'tbl_rnt_ss_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_type');
    }
}

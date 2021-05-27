<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand', function (Blueprint $table) {
            $table->bigIncrements('br_id');
            $table->bigInteger('br_ss_id')->unsigned();
            $table->foreign('br_ss_id', 'tbl_br_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('br_name', 150);
            $table->char('br_active', 1)->default('Y');
            $table->bigInteger('br_created_by');
            $table->dateTime('br_created_on');
            $table->bigInteger('br_updated_by')->nullable();
            $table->dateTime('br_updated_on')->nullable();
            $table->bigInteger('br_deleted_by')->nullable();
            $table->dateTime('br_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brand');
    }
}

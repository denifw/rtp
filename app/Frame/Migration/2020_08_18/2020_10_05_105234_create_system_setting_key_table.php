<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemSettingKeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_setting_key', function (Blueprint $table) {
            $table->bigIncrements('ssk_id');
            $table->bigInteger('ssk_ss_id')->unsigned();
            $table->foreign('ssk_ss_id', 'tbl_ssk_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('ssk_api_key',255);
            $table->bigInteger('ssk_created_by');
            $table->dateTime('ssk_created_on');
            $table->bigInteger('ssk_updated_by')->nullable();
            $table->dateTime('ssk_updated_on')->nullable();
            $table->bigInteger('ssk_deleted_by')->nullable();
            $table->dateTime('ssk_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_setting_key');
    }
}

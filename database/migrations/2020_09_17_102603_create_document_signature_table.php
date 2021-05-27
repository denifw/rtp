<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentSignatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_signature', function (Blueprint $table) {
            $table->bigIncrements('ds_id');

            $table->bigInteger('ds_ss_id')->unsigned();
            $table->foreign('ds_ss_id','tbl_ap_ss_id_foreign')->references("ss_id")->on("system_setting");

            $table->bigInteger('ds_dt_id')->unsigned();
            $table->foreign('ds_dt_id','tbl_ds_dt_id_foreign')->references("dt_id")->on("document_template");

            $table->bigInteger('ds_cp_id')->unsigned();
            $table->foreign('ds_cp_id','tbl_ds_cp_id_foreign')->references("cp_id")->on("contact_person");

            $table->bigInteger('ds_created_by');
            $table->dateTime('ds_created_on');

            $table->bigInteger('ds_updated_by')->nullable();
            $table->dateTime('ds_updated_on')->nullable();

            $table->bigInteger('ds_deleted_by')->nullable();
            $table->dateTime('ds_deleted_on')->nullable();
            $table->string('ds_deleted_reason', 255)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_signature');
    }
}

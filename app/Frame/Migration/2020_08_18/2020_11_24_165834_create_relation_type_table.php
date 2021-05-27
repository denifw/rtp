<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relation_type', function (Blueprint $table) {
            $table->bigIncrements('rty_id');
            $table->bigInteger('rty_rel_id')->unsigned();
            $table->foreign('rty_rel_id', 'tbl_rty_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('rty_sty_id')->unsigned();
            $table->foreign('rty_sty_id', 'tbl_rty_sty_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('rty_created_by');
            $table->dateTime('rty_created_on');
            $table->bigInteger('rty_updated_by')->nullable();
            $table->dateTime('rty_updated_on')->nullable();
            $table->bigInteger('rty_deleted_by')->nullable();
            $table->dateTime('rty_deleted_on')->nullable();
            $table->string('rty_deleted_reason', 256)->nullable();
            $table->uuid('rty_uid');
            $table->unique('rty_uid', 'tbl_rty_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relation_type');
    }
}

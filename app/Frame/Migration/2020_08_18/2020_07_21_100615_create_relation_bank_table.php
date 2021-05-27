<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relation_bank', function (Blueprint $table) {
            $table->bigIncrements('rb_id');
            $table->bigInteger('rb_rel_id')->unsigned();
            $table->foreign('rb_rel_id', 'tbl_rb_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('rb_bn_id')->unsigned();
            $table->foreign('rb_bn_id', 'tbl_rb_bn_id_foreign')->references('bn_id')->on('bank');
            $table->string('rb_number', 255);
            $table->string('rb_branch', 255);
            $table->string('rb_name', 255);
            $table->char('rb_active', 1)->default('Y');
            $table->bigInteger('rb_created_by');
            $table->dateTime('rb_created_on');
            $table->bigInteger('rb_updated_by')->nullable();
            $table->dateTime('rb_updated_on')->nullable();
            $table->string('rb_deleted_reason', 255)->nullable();
            $table->bigInteger('rb_deleted_by')->nullable();
            $table->dateTime('rb_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relation_bank');
    }
}

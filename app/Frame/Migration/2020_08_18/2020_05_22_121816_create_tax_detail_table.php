<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_detail', function (Blueprint $table) {
            $table->bigIncrements('td_id');
            $table->bigInteger('td_tax_id')->unsigned()->nullable();
            $table->foreign('td_tax_id', 'tbl_td_tax_id_foreign')->references('tax_id')->on('tax');
            $table->string('td_name', 125);
            $table->float('td_percent');
            $table->char('td_active', 1)->default('Y');
            $table->bigInteger('td_created_by');
            $table->dateTime('td_created_on');
            $table->bigInteger('td_updated_by')->nullable();
            $table->dateTime('td_updated_on')->nullable();
            $table->bigInteger('td_deleted_by')->nullable();
            $table->dateTime('td_deleted_on')->nullable();
            $table->unique(['td_tax_id', 'td_name'], 'tbl_td_tax_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_detail');
    }
}

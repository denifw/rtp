<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('port', function (Blueprint $table) {
            $table->bigIncrements('po_id');
            $table->string('po_name', 255);
            $table->bigInteger('po_cnt_id')->unsigned();
            $table->foreign('po_cnt_id', 'tbl_po_cnt_id_foreign')->references('cnt_id')->on('country');
            $table->bigInteger('po_cty_id')->nullable();
            $table->foreign('po_cty_id', 'tbl_po_cty_id_foreign')->references('cty_id')->on('city');
            $table->bigInteger('po_tm_id');
            $table->foreign('po_tm_id', 'tbl_po_tm_id_foreign')->references('tm_id')->on('transport_module');
            $table->string('po_code', 50)->nullable();
            $table->char('po_active', 1)->default('Y');
            $table->bigInteger('po_created_by');
            $table->dateTime('po_created_on');
            $table->bigInteger('po_updated_by')->nullable();
            $table->dateTime('po_updated_on')->nullable();
            $table->bigInteger('po_deleted_by')->nullable();
            $table->dateTime('po_deleted_on')->nullable();
            $table->unique('po_code', 'tbl_po_code_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => PortSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('port');
    }
}

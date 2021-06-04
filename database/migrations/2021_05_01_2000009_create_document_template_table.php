<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_template', function (Blueprint $table) {
            $table->uuid('dt_id')->primary();
            $table->uuid('dt_dtt_id')->unsigned();
            $table->foreign('dt_dtt_id', 'tbl_dt_dtt_id_fkey')->references('dtt_id')->on('document_template_type');
            $table->string('dt_description', 255);
            $table->string('dt_path', 255);
            $table->string('dt_preview_path', 255)->nullable();
            $table->char('dt_active', 1)->default('Y');
            $table->uuid('dt_created_by');
            $table->dateTime('dt_created_on');
            $table->uuid('dt_updated_by')->nullable();
            $table->dateTime('dt_updated_on')->nullable();
            $table->uuid('dt_deleted_by')->nullable();
            $table->dateTime('dt_deleted_on')->nullable();
            $table->unique(['dt_dtt_id', 'dt_path'], 'tbl_dtt_id_path_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_template');
    }
}

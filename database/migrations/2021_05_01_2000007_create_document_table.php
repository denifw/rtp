<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document', function (Blueprint $table) {
            $table->uuid('doc_id')->primary();
            $table->uuid('doc_ss_id')->unsigned();
            $table->foreign('doc_ss_id', 'tbl_doc_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->uuid('doc_dct_id')->unsigned();
            $table->foreign('doc_dct_id', 'tbl_doc_dct_id_fkey')->references('dct_id')->on('document_type');
            $table->uuid('doc_group_reference');
            $table->uuid('doc_type_reference')->nullable();
            $table->string('doc_file_name', 256);
            $table->string('doc_description', 256)->nullable();
            $table->double('doc_file_size');
            $table->string('doc_file_type', 128);
            $table->char('doc_public', 1)->default('Y');
            $table->uuid('doc_created_by');
            $table->dateTime('doc_created_on');
            $table->uuid('doc_updated_by')->nullable();
            $table->dateTime('doc_updated_on')->nullable();
            $table->uuid('doc_deleted_by')->nullable();
            $table->dateTime('doc_deleted_on')->nullable();
            $table->string('doc_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document');
    }
}

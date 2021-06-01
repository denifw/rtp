<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageRightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_right', function (Blueprint $table) {
            $table->uuid('pr_id')->primary();
            $table->uuid('pr_pg_id')->unsigned();
            $table->foreign('pr_pg_id', 'tbl_pr_pg_id_fkey')->references('pg_id')->on('page');
            $table->string('pr_name', 128);
            $table->string('pr_description', 256);
            $table->char('pr_default', 1)->default('N');
            $table->char('pr_active', 1)->default('Y');
            $table->dateTime('pr_created_on');
            $table->uuid('pr_created_by');
            $table->dateTime('pr_updated_on')->nullable();
            $table->uuid('pr_updated_by')->nullable();
            $table->dateTime('pr_deleted_on')->nullable();
            $table->uuid('pr_deleted_by')->nullable();
            $table->string('pr_deleted_reason', 256)->nullable();
            $table->unique(['pr_pg_id', 'pr_name'], 'tbl_pr_pg_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_right');
    }
}

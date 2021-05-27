<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_type', function (Blueprint $table) {
            $table->bigIncrements('sty_id');
            $table->string('sty_group', 256);
            $table->string('sty_name', 256);
            $table->char('sty_active', 1)->default('Y');
            $table->bigInteger('sty_created_by');
            $table->dateTime('sty_created_on');
            $table->bigInteger('sty_updated_by')->nullable();
            $table->dateTime('sty_updated_on')->nullable();
            $table->bigInteger('sty_deleted_by')->nullable();
            $table->dateTime('sty_deleted_on')->nullable();
            $table->string('sty_deleted_reason', 256)->nullable();
            $table->unique(['sty_group', 'sty_name'], 'tbl_sty_group_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_type');
    }
}

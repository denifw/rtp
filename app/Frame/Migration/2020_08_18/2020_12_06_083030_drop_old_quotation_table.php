<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropOldQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('quotation_warehouse');
        Schema::dropIfExists('quotation_truck');
        Schema::dropIfExists('quotation_forwarding');
        Schema::dropIfExists('quotation_clearance');
        Schema::dropIfExists('quotation_request');
        Schema::dropIfExists('quotation_detail');
        Schema::dropIfExists('quotation');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

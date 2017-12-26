<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSystemsCommissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if (!Schema::hasTable('ps_commission')) {
		    Schema::create('ps_commission', function (Blueprint $table) {
			    $table->increments('id');
			    $table->integer('wallet_id');
			    $table->integer('payment_system_id');
			    $table->string('currency', 6);
			    $table->double('commission', 15, 7);
			    $table->boolean('active')->default(true);
			    $table->boolean('is_deleted')->default(false);
			    $table->timestamps();
		    });
	    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::dropIfExists('ps_commission');
    }
}
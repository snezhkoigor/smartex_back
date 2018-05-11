<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if (!Schema::hasTable('payments')) {
		    Schema::create('payments', function (Blueprint $table) {
			    $table->increments('id');
			    $table->integer('id_user');
			    $table->integer('id_account');
			    $table->dateTime('date');
			    $table->integer('type');
			    $table->string('payment_system', 16);
			    $table->string('payer', 255)->nullable();
			    $table->string('payee', 255)->nullable();
			    $table->integer('id_user_details')->nullable();
			    $table->double('amount', 15, 8);
			    $table->string('currency', 6);
			    $table->double('fee', 15, 8)->nullable();
			    $table->string('batch', 255)->nullable();
			    $table->dateTime('date_confirm')->nullable();
			    $table->text('comment')->nullable();
			    $table->boolean('confirm')->default(false);
			    $table->boolean('btc_check')->default(false);
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
	    Schema::dropIfExists('payments');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if (!Schema::hasTable('exchanges')) {
		    Schema::create('exchanges', function (Blueprint $table) {
			    $table->increments('id');
			    $table->dateTime('date');
			    $table->integer('id_user');
			    $table->string('in_payment', 16);
			    $table->integer('in_id_pay');
			    $table->string('in_currency', 6);
			    $table->double('in_amount', 15, 8);
			    $table->double('in_fee', 15, 8)->nullable();
			    $table->string('in_payee', 61)->nullable();
			    $table->integer('in_discount')->nullable();
			    $table->text('comment')->nullable();
			    $table->string('out_payment', 16);
			    $table->integer('out_id_pay')->nullable();
			    $table->string('out_currency', 6);
			    $table->double('out_amount', 15, 8);
			    $table->string('out_payee', 61)->nullable();
			    $table->string('out_payer', 61)->nullable();
			    $table->double('out_fee', 15, 8)->nullable();
			    $table->string('out_batch', 255)->nullable();
			    $table->dateTime('out_date')->nullable();
			    $table->integer('rating')->default(0);
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
	    Schema::dropIfExists('exchanges');
    }
}

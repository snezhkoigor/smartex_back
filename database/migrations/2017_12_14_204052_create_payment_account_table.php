<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if (!Schema::hasTable('payment_account')) {
		    Schema::create('payment_account', function (Blueprint $table) {
			    $table->increments('id');
			    $table->integer('payment_system_id');
			    $table->string('ps_type', 16);
			    $table->string('currency', 6);
			    $table->text('account');
			    $table->text('user')->nullable();
			    $table->text('password')->nullable();
			    $table->text('secret')->nullable();
			    $table->string('adv_sci', 100)->nullable();
			    $table->string('id_payee', 100)->nullable();
			    $table->double('balance', 15, 8)->default(0)->nullable();
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
	    Schema::dropIfExists('payment_account');
    }
}

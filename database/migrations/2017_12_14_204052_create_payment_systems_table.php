<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if (!Schema::hasTable('payment_systems')) {
		    Schema::create('payment_systems', function (Blueprint $table) {
			    $table->increments('id');
			    $table->string('name', 100);
			    $table->string('logo', 255)->nullable();
			    $table->string('fields', 255)->nullable();
			    $table->string('code', 16)->nullable();
			    $table->boolean('active')->default(true);
			    $table->boolean('is_account_multi_line')->default(false);
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
	    Schema::dropIfExists('payment_systems');
    }
}

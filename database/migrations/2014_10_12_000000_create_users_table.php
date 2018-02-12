<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer('refer')->nullable();
            $table->string('name', 100)->nullable();
	        $table->string('family', 100)->nullable();
	        $table->string('lang', 15)->nullable();
	        $table->string('country', 50)->nullable();
	        $table->boolean('activation')->default(false);
            $table->string('email', 50)->unique();
            $table->string('password', 100);
	        $table->integer('auth_err')->nullable();
	        $table->dateTime('auth_err_date')->nullable();
	        $table->string('auth_err_ip', 50)->nullable();
	        $table->string('ip', 50)->nullable();
	        $table->dateTime('online')->nullable();
	        $table->string('role', 50)->nullable();
	        $table->integer('discount')->nullable();
	        $table->double('total_exchange', 10, 2)->nullable();
	        $table->string('document_number', 50)->nullable();
	        $table->string('verification_image', 255)->nullable();
	        $table->boolean('verification_ok')->default(false);
	        $table->string('avatar', 255)->nullable();
	        $table->string('comment', 255)->nullable();
	        $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

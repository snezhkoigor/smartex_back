<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableLoginLogsCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('browser', 255)->nullable();
            $table->string('tech_browser_info', 255)->nullable();
            $table->ipAddress('ip')->nullable();
            $table->text('geo')->nullable();
            $table->text('token_id')->nullable();
            $table->text('token')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('login_logs');
    }
}

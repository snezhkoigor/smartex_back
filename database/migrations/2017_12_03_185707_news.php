<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class News extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if (!Schema::hasTable('news')) {
		    Schema::create('news', function (Blueprint $table) {
			    $table->increments('id');
			    $table->date('date');
			    $table->string('title', 255);
			    $table->string('meta_key', 255)->nullable();
			    $table->string('meta_description', 255)->nullable();
			    $table->text('text');
			    $table->boolean('active')->default(true);
			    $table->boolean('is_delete')->default(false);
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
	    Schema::dropIfExists('news');
    }
}

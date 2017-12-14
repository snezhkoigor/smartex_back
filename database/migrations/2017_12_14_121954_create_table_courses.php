<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCourses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if (!Schema::hasTable('courses')) {
		    Schema::create('courses', function (Blueprint $table) {
			    $table->increments('id');
			    $table->date('date');
			    $table->string('in_currency', 6);
			    $table->string('out_currency', 6);
			    $table->double('course', 15, 7);
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
	    Schema::dropIfExists('courses');
    }
}

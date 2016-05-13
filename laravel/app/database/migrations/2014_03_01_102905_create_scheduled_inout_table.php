<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledInOutTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scheduled_inout', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('associate_id', 6)->nullable();
			$table->integer('store_id');
			$table->dateTime('date_in')->nullable();
			$table->dateTime('date_out')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scheduled_inout');
	}

}

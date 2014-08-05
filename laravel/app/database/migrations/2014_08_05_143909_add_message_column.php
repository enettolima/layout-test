<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMessageColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('sqlsrv_ebt')->table('weather_alerts', function(Blueprint $table)
		{
            $table->text('message')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('weather_alerts', function(Blueprint $table)
		{
			//
		});
	}

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeatherAlertsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('sqlsrv_ebt')->create('weather_alerts', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('store_code');
            $table->string('city');
            $table->string('state', 2);
            $table->char('timezone', 3)->nullable();
            $table->boolean('all_clear')->nullable();
            $table->char('type', 3)->nullable();
            $table->string('description')->nullable();
            $table->datetime('alert_date')->nullable();
            $table->datetime('alert_expires')->nullable();
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
		Schema::drop('weather_alerts');
	}

}

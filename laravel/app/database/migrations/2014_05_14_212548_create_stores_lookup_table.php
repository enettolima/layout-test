<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresLookupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stores_lookup', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('code')->unique();
            $table->string('store_name');
            $table->string('street')->nullable();
            $table->string('ste')->nullable();
            $table->string('state', 2);
            $table->string('city');
            $table->string('zip')->nullable();
            $table->string('phone', 20)->nullable();
            $table->integer('tz_offset')->nullable();
            $table->boolean('is_tourist')->nullable();
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
		Schema::drop('stores_lookup');
	}

}

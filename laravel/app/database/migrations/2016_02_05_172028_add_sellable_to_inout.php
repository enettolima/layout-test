<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSellableToInout extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scheduled_inout', function(Blueprint $table)
		{
			//Adding sql id and sellable flag so we can sync with sql server
			$table->integer('sql_id')->default(0);
			$table->tinyInteger('sellable')->default(1);//1=sellable 0=non-sellable
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scheduled_inout', function(Blueprint $table)
		{
			//
		});
	}

}

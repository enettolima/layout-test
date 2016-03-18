<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebordersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('weborders', function(Blueprint $table)
		{
			$table->increments('id');
			$table->char('week_of', 10);
			$table->char('store', 3);
			$table->bigInteger('item_id');
			$table->tinyInteger('item_qty');
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
		Schema::drop('weborders');
	}

}

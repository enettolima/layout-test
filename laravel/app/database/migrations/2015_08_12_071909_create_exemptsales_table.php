<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExemptsalesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('exemptsales', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('empl_id');
            $table->string('store_id');
            $table->string('receipt_num');
            $table->string('files');
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
		Schema::drop('exemptsales');
	}

}

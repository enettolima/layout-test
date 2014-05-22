<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesLookupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employees_lookup', function(Blueprint $table)
		{
			$table->increments('id');
            $table->boolean('active');
            $table->string('description', 30)->nullable();
            $table->string('empl_id');
            $table->string('empl_name', 10);
            $table->string('empl_no1', 10)->nullable();
            $table->string('empl_no2', 1)->nullable();
            $table->string('rpro_full_name', 100)->nullable();
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
		Schema::table('employees_lookup', function(Blueprint $table)
		{
			//
		});
	}

}

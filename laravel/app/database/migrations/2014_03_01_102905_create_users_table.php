<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('password', 60)->nullable();
            $table->string('email')->nullable();
            $table->string('username', 64)->nullable();
            $table->unique('username', 'username_unique');
            $table->string('defaultStore', 3)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->boolean('rpro_user')->nullable();
            $table->integer('rpro_id')->nullable();
            $table->unique('rpro_id')->nullable();
            $table->string('full_name')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->string('preferred_email')->nullable();
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
		Schema::drop('users');
	}

}

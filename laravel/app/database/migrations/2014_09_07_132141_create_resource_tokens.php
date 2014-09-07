<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceTokens extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('resource_tokens', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('creator_user_id');
            $table->boolean('active');
            $table->dateTime('expires_at');
            $table->string('resource');
            $table->string('token', 20);
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
		Schema::drop('resource_tokens');
	}

}

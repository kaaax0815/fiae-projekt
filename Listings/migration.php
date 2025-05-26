<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('LORA_sensors', function (Blueprint $table) {
			$table->id();
			$table->foreignId('lora_device_id')->references('id')->on('LORA_devices');
			$table->foreignId('lora_sensor_type_id')->references('id')->on('LORA_sensor_types');
			$table->string('name')->nullable();
			$table->string('location')->nullable();
			$table->float('min')->nullable();
			$table->float('max')->nullable();
			$table->integer('decimals')->nullable();
			$table->string('prefix')->nullable();
			$table->string('suffix')->nullable();
			$table->text('formula')->nullable();
			$table->json('config')->nullable();
			$table->text('general_info')->nullable();
			$table->boolean('published')->default(false);
			$table->boolean('active')->default(true);
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
		Schema::dropIfExists('LORA_sensors');
	}
};

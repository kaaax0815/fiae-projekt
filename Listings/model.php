<?php

namespace App\Models\Lora;

use App\Models\Admin\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LORASensor extends Model
{
	use HasFactory;

	protected $table = 'LORA_sensors';

	protected $fillable = [
		'name',
		'location',
		'min',
		'max',
		'decimals',
		'prefix',
		'suffix',
		'formula',
		'config',
		'general_info',
		'published',
		'active',

		'lora_device_id',
		'lora_sensor_type_id'
	];

	public static $validator = [
		'name' => 'string',
		'location' => 'string',
		'min' => 'float',
		'max' => 'float',
		'decimals' => 'integer',
		'prefix' => 'string',
		'suffix' => 'string',
		'formula' => 'string',
		'config' => 'json',
		'general_info' => 'string',
		'published' => 'boolean',
		'active' => 'boolean',
	];

	protected $casts = [
		'name' => 'string',
		'location' => 'string',
		'min' => 'float',
		'max' => 'float',
		'decimals' => 'integer',
		'prefix' => 'string',
		'suffix' => 'string',
		'formula' => 'string',
		'config' => 'json',
		'general_info' => 'string',
		'published' => 'boolean',
		'active' => 'boolean',
	];


	public function lora_device() {
		return $this->belongsTo(LORADevice::class, 'lora_device_id', 'id');
	}

	public function lora_sensor_type() {
		return $this->belongsTo(LORASensorType::class, 'lora_sensor_type_id', 'id');
	}

	public function lora_temperatures() {
		return $this->hasMany(LORATemperature::class, 'lora_sensor_id', 'id');
	}

	public function categoryMapping() {
		return $this->hasMany(LORACategorySensor::class, 'sensor_id', 'id');
	}
}

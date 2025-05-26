<?php

namespace App\Http\Controllers\Lora;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Interfaces\ChirpstackController;
use App\Models\Lora\LORADevice;
use App\Models\Lora\LORADeviceProfile;
use App\Models\Lora\LORASensor;
use App\Resources\Helpers;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoraDevicesController extends Controller
{
	private $m = LORADevice::class;

	public function show(Request $request, int $id) {
		$userTypeId = Auth::user()->user_type_id;
		if ($userTypeId !== 1) {
			return response()->json(['message' => 'Not allowed'], 403);
		}
		
		$device = $this->m::find($id);
		if (!isset($device)) {
			return response()->json(['message' => 'Device not found'], 404);
		}
		
		return [
			'id' => $device->id,
			'published' => $device->published ? 1 : 0,
			'deviceProfileId' => $device->device_profile_id,
			'devEui' => $device->devEui,
			'joinEui' => $device->join_eui,
			'appKey' => $device->app_key,
			'serialNumber' => $device->serial_number,
			'battery' => $device->battery,
			'lastSeenAt' => $device->last_seen_at,
			'generalInfo' => $device->general_info,
		];
	}

	public function store(Request $request) {
		$userTypeId = Auth::user()->user_type_id;
		if ($userTypeId !== 1) {
			return response()->json(['message' => 'Not allowed'], 403);
		}
		
		$values = $request->all();
		$inp = Helpers::updateArrayKeys($values, [
			'published' => 'published',
			'deviceProfileId' => 'device_profile_id',
			'devEui' => 'devEui',
			'joinEui' => 'join_eui',
			'appKey' => 'app_key',
			'serialNumber' => 'serial_number',
			'generalInfo' => 'general_info',
		]);
		$validation = Helpers::requireArrayKeys($inp, ['published', 'device_profile_id', 'devEui', 'app_key']);
		if ($validation->fails()) {
			return response()->json(['status' => -1, 'message' => $validation->errors()], 422);
		}
		
		$inp['devEui'] = strtolower($inp['devEui']);
		$chirpstack = new ChirpstackController();
		
		$deviceProfile = LORADeviceProfile::where('active', 1)->find($inp['device_profile_id']);
		if (!isset($deviceProfile)) {
			return response()->json(['message' => 'Invalid Device Profile supplied'], 422);
		}
		
		try {
			if ($chirpstack->addDevice($inp['devEui'], $inp['devEui'], $inp['app_key'], $deviceProfile->uuid) === null) {
				Log::warning('addDevice failed '.$inp['devEui']);
				return response()->json(['status' => -1, 'message' => 'Device creation failed'], 500);
			}
		} catch (GuzzleException $e) {
			return response()->json(['status' => -1, 'message' => $e->getMessage()], $e->getCode());
		}
		
		$device = $this->m::create($inp);
		foreach ($deviceProfile->sensorTypes as $sensorType) {
			LORASensor::create([
				'lora_device_id' => $device->id,
				'lora_sensor_type_id' => $sensorType->id,
			]);
		}
		
		return ['status' => 0, 'id' => $device->id];
	}
}

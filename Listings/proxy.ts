import { promisify } from 'node:util';
import { createError, createRouter, eventHandler, readValidatedBody } from 'h3';
import { DeviceServiceClient } from '@chirpstack/chirpstack-api/api/device_grpc_pb.js';
import Device from '@chirpstack/chirpstack-api/api/device_pb.js';
import { ChannelCredentials } from '@grpc/grpc-js';

import { ADDRESS, APPLICATION_ID, metadata } from './globals.js';
import { responseHandler } from './utils.js';
import { CreateDeviceSchema } from './schema.js';

const devicesRouter = createRouter();

async function createDevice(
	devEui: string,
	name: string,
	appKey: string,
	deviceProfileId: string
) {
	const client = new DeviceServiceClient(
		ADDRESS,
		ChannelCredentials.createSsl()
	);

	const request = new Device.CreateDeviceRequest();
	const device = new Device.Device();
	device.setDevEui(devEui);
	device.setName(name);
	device.setDeviceProfileId(deviceProfileId);
	device.setApplicationId(APPLICATION_ID);
	request.setDevice(device);
	await responseHandler(
		promisify(client.create.bind(client, request, metadata, {}))()
	);
	const requestK = new Device.CreateDeviceKeysRequest();
	const deviceKeys = new Device.DeviceKeys();
	deviceKeys.setDevEui(devEui);
	// LoraWAN 1.1 renamed AppKey to NwkKey
	deviceKeys.setNwkKey(appKey);
	requestK.setDeviceKeys(deviceKeys);
	return responseHandler(
		promisify(client.createKeys.bind(client, requestK, metadata, {}))()
	);
}

devicesRouter.post(
	'/create',
	eventHandler(async (ev) => {
		const body = await readValidatedBody(ev, CreateDeviceSchema.safeParse);
		if (!body.success) {
			return createError({
				statusCode: 422,
				statusMessage: 'Unprocessable Content',
				data: { details: body.error.message },
			});
		}
		const response = await createDevice(
			body.data.devEui,
			body.data.name,
			body.data.appKey,
			body.data.deviceProfileId
		);

		return response.toObject();
	})
);

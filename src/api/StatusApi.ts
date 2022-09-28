import axios, {AxiosResponse} from "axios";
import { baseUrl, baseConfig } from "./Api";
import { statusType } from 'breezeTypes';

export interface ServerStatusResponse {
	data: ServerStatusData
}

interface ServerStatusData {
	users: object
	status: Array<statusType>
}

const action = 'breezeStatus';

export const getByProfile = () =>
{
	return axios.get<ServerStatusData>(
		baseUrl(action, 'statusByProfile'),
		baseConfig(),
	)
}

export const deleteStatus = (statusId:Number) =>
{
	return axios.delete(baseUrl(action, 'deleteStatus', {'id': statusId}))
}


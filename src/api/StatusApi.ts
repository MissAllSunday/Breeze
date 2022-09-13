import axios, {AxiosResponse} from "axios";
import { baseUrl, baseParams } from "./Api";
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
		{ data: baseParams()},
	)
}


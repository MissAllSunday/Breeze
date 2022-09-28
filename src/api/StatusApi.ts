import axios from "axios";
import { baseUrl, baseConfig } from "./Api";
import { statusType } from 'breezeTypes';
import smfVars from "../DataSource/SMF";

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
		baseUrl(action, 'statusByProfile', [{
			wallId: smfVars.wallId
		}]),
		baseConfig(),
	)
}

export const deleteStatus = (statusId:Number) =>
{
	// SMF cannot handle custom methods without changing some settings, thus, we are going to use POST for delete calls
	return axios.post(baseUrl(action, 'deleteStatus'), baseConfig({
		id: statusId,
		userId: parseInt(smfVars.userId)
	}));
}


import axios from "axios";
import { baseUrl, baseConfig } from "./Api";
import { statusType, statusListType } from 'breezeTypes';
import smfVars from "../DataSource/SMF";

export interface ServerStatusResponse {
	data: statusListType
}

const action = 'breezeStatus';

export const getByProfile = () =>
{
	return axios.get<statusListType>(
		baseUrl(action, 'statusByProfile', [{
			wallId: smfVars.wallId
		}]),
		baseConfig(),
	)
}

export const deleteStatus = (statusId:number) =>
{
	// SMF cannot handle custom methods without changing some settings, thus, we are going to use POST for delete calls
	return axios.post(baseUrl(action, 'deleteStatus'), baseConfig({
		id: statusId,
		userId: smfVars.userId
	}));
}


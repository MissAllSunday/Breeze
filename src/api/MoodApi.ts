import axios, {AxiosResponse} from "axios";
import {baseConfig, baseUrl} from "./Api";
import { moodType } from 'breezeTypes';

const action = 'breezeMood'

export interface ServerMoodResponse {
	data: Array<moodType>
}

export const getActiveMoods = () =>
{
	return axios.get<ServerMoodResponse>(
		baseUrl(action, 'getActiveMoods'),
		baseConfig(),
	)
}

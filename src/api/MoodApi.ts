import {AxiosResponse} from "axios";
import { moodType } from 'breezeTypes';
import Utils from "../Utils";

const action = 'breezeMood'
const responseBody = (response: AxiosResponse) => response.data;

const moodRequests = {
	get: (action: string, subAction: string) => Utils.api().get<moodType>(Utils.buildBaseUrlWithParams(action, subAction).href)
};

export const MoodApi =
{
	getActiveMoods : () => moodRequests.get(action, 'getActiveMoods')
}

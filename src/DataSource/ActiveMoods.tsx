import { moodType } from 'breezeTypes';
import Utils from "../Utils";
import {AxiosResponse} from "axios";

let action = 'breezeMood'
let subActions = {
	all: 'getAllMoods',
	active: 'getActiveMoods',
	eliminate: 'deleteMood',
	post: 'postMood',
	setMood: 'setUserMood'
}

export default function ActiveMoods(): Promise<AxiosResponse<Array<any>>> {

	let callUrl = Utils.buildBaseUrlWithParams(action, subActions.active)

	try {
		return  Utils.api().get(callUrl.href)
	} catch (error: any) {
		return error.message
	}

};

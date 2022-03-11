import { moodType } from 'breezeTypes';
import Utils from "../Utils";

let action = 'breezeMood'
let subActions = {
	all: 'getAllMoods',
	active: 'getActiveMoods',
	eliminate: 'deleteMood',
	post: 'postMood',
	setMood: 'setUserMood'
}

export default async function ActiveMoods(): Promise<moodType[]> {

	let callUrl = Utils.buildBaseUrlWithParams(action, subActions.active)

	try {
		return await Utils.api().get(callUrl.href)
	} catch (error: any) {
		return error.message
	}

};

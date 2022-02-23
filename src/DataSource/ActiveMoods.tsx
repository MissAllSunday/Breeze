import Mood from "../components/Mood";
import {AxiosResponse} from "axios";
import React, { useState, useEffect } from 'react';
import utils from "../Utils";
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

export default function  ActiveMoods(): moodType[] {
	const [moodData, setMoodData] = useState([] as any);

	let baseUrl = Utils.buildBaseUrlWithParams()
	baseUrl.searchParams.append('action', action)
	baseUrl.searchParams.append('sa', subActions.active)

	useEffect(() => {
		utils.api().get(baseUrl.href)
			.then(function(response:AxiosResponse) {
				// @ts-ignore
				setMoodData(response.data.content)
			})
			.catch(exception => {
				console.log(exception);
			}).then(() => {
		})
	}, [baseUrl.href]);

	let listMoods = moodData.map((mood: moodType) =>
		<Mood
			canUseMood={false}
			isCurrentUserOwner={false}
			userMoodId={0} />
	)

	return (listMoods);
};

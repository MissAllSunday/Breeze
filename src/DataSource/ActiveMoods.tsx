import Mood from "../components/Mood";
import {AxiosResponse} from "axios";
import React, { useState, useEffect } from 'react';
import utils from "../Utils";
import { moodType } from 'breezeTypes';

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

	console.log(utils.sprintFormat([action, subActions.all]));

	useEffect(() => {
		utils.api().get(utils.sprintFormat([action, subActions.all]))
			.then(function(response:AxiosResponse) {
				// @ts-ignore
				setMoodData(response.data.content)
			})
			.catch(exception => {
				console.log(exception);
			}).then(() => {
		})
	}, []);

	let listMoods = moodData.map((mood: moodType) =>
		<Mood
			canUseMood={false}
			isCurrentUserOwner={false}
			userMoodId={0} />
	)

	return (listMoods);
};

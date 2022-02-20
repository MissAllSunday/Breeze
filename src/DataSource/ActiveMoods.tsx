import Mood from "../components/Mood";
import axios, {AxiosRequestConfig, AxiosResponse, AxiosResponseHeaders} from "axios";
import React, { useState, useEffect } from 'react';
import Utils from "../Utils";
import { moodType } from 'breezeTypes';
import SMF from "./SMF";

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
	const [fetching, setFetching] = useState(false);

	useEffect(() => {
		setFetching(true);

		Utils.api.get(Utils.sprintFormat([action, subActions.all]))
			.then(function(response:AxiosResponse) {
				// @ts-ignore
				setMoodData(response.data.content)
			})
			.catch(exception => {
				console.log(exception);
			}).then(() => {
				setFetching(false)
		})
	});
	let smfVars = SMF()

	let listMoods = moodData.map((mood: moodType) =>
		<Mood
			canUseMood={false}
			isCurrentUserOwner={false}
			moodTxt={smfVars.txt.moodTxt}
			userId={smfVars.userId}
			userMoodId={0} />
	)

	return (listMoods);
};

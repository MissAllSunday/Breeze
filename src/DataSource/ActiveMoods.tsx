import Mood from "../components/Mood";
import {AxiosResponse} from "axios";
import React, {useEffect, useState} from 'react';
import utils from "../Utils";
import Utils from "../Utils";
import {moodType} from 'breezeTypes';

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
	const [isLoading, setIsLoading] = useState(true);

	let baseUrl = Utils.buildBaseUrlWithParams()
	baseUrl.searchParams.append('action', action)
	baseUrl.searchParams.append('sa', subActions.active)

	useEffect(() => {
		utils.api().get(baseUrl.href)
			.then(function(response:AxiosResponse) {
				// @ts-ignore
				let responseData = Object.values(response.data)

				setMoodData(responseData)
			})
			.catch(exception => {
				console.log(exception);
			}).then(() => {
			setIsLoading(false)
		})
	}, [baseUrl.href]);

	if (!isLoading) {
		return moodData.map((mood: moodType) =>
			<Mood
				canUseMood={false}
				isCurrentUserOwner={false}
				userMoodId={0}/>
		)
	}

	return []
};

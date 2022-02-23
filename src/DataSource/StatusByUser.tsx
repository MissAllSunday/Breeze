import {AxiosResponse} from "axios";
import React, { useState, useEffect } from 'react';
import Status from "../components/Status";
import { statusType } from 'breezeTypes';
import Utils from "../Utils";
import Smf from "./SMF";

let action = 'breezeStatus'
let subActions = {
	post: 'postStatus',
	byProfile: 'statusByProfile',
	eliminate: 'deleteStatus',
}

export default function  StatusByUser(): any {
	const [statusData, setStatusData] = useState([] as any);
	const [usersData, setUsersData] = useState([] as any);
	const [isLoading, setIsLoading] = useState(true);

	let smfVars = Smf
	let baseUrl = Utils.buildBaseUrlWithParams()
	baseUrl.searchParams.append('action', action)
	baseUrl.searchParams.append('sa', subActions.byProfile)
	baseUrl.searchParams.append('wallId', smfVars.wallId)

	useEffect(() => {
		Utils.api().get(baseUrl.href)
			.then(function(response:AxiosResponse) {
				// @ts-ignore
				setStatusData(Object.values(response.data.status))
				// @ts-ignore
				setUsersData(Object.entries(response.data.users))

				setIsLoading(false)
			})
			.catch(exception => {
				console.log(exception);
			});
	}, [baseUrl.href]);

	if (isLoading) {
		return 'lol'
	}

	else {
window.console.log(statusData)
		let listStatus = statusData.map((status: statusType) =>
			<Status
				key={status.id}
				status={status}
				users={usersData}
				removeStatus={onRemoveStatus}
				setNewUsers={onSetNewUsers}
			/>
		)

		return (listStatus);
	}
};

function onRemoveStatus()
{

}

function onSetNewUsers()
{

}

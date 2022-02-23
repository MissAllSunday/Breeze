import {AxiosResponse} from "axios";
import React, { useState, useEffect } from 'react';
import Status from "../components/Status";
import Utils from "../Utils";
import Smf from "./SMF";

let action = 'breezeStatus'
let subActions = {
	post: 'postStatus',
	byProfile: 'statusByProfile',
	eliminate: 'deleteStatus',
}

export default function  StatusByUser(): Status[] {
	const [statusData, setStatusData] = useState([] as any);
	const [usersData, setUsersData] = useState([] as any);

	let smfVars = Smf
	let baseUrl = Utils.buildBaseUrlWithParams()
	baseUrl.searchParams.append('action', action)
	baseUrl.searchParams.append('sa', subActions.byProfile)
	baseUrl.searchParams.append('wallId', smfVars.wallId)

	useEffect(() => {
		Utils.api().get(baseUrl.href)
			.then(function(response:AxiosResponse) {
				// @ts-ignore
				setStatusData(response.data.content.status)

				// @ts-ignore
				setUsersData(response.data.content.users)
			})
			.catch(exception => {
				console.log(exception);
			});
	}, []);

	let listStatus = statusData.map((status: {status_id: ''}) =>
		<Status
			key={status.status_id}
			status={status}
			users={usersData}
			removeStatus={onRemoveStatus}
			setNewUsers={onSetNewUsers}
		/>
	)

	return (listStatus);
};

function onRemoveStatus()
{

}

function onSetNewUsers()
{

}

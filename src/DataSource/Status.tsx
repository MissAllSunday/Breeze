import axios, {AxiosRequestConfig, AxiosResponse, AxiosResponseHeaders} from "axios";
import React, { useState, useEffect } from 'react';
import Status from "../components/Status";
import Utils from "../Utils";

let action = 'breezeStatus'
let subActions = {
	post: 'postStatus',
	byProfile: 'statusByProfile',
	eliminate: 'deleteStatus',
}

export default function  statusByUser(): Status[] {
	const [statusData, setStatusData] = useState([] as any);
	const [usersData, setUsersData] = useState([] as any);
	const [fetching, setFetching] = useState(false);

	useEffect(() => {
		setFetching(true);

		Utils.api.get(Utils.sprintFormat([action, subActions.byProfile]))
			.then(function(response:AxiosResponse) {
				setFetching(false);
				// @ts-ignore
				setStatusData(response.data.content.status)

				// @ts-ignore
				setUsersData(response.data.content.users)
			})
			.catch(exception => {
				console.log(exception);
				setFetching(false);
			});
	});

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

import {AxiosResponse} from "axios";
import React, { useState, useEffect } from 'react';
import Status from "../components/Status";
import { statusType } from 'breezeTypes';
import Utils from "../Utils";

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

	useEffect(() => {
		let baseUrl = Utils.buildBaseUrlWithParams(action, subActions.byProfile)

		Utils.api().get(baseUrl.href)
			.then((response:AxiosResponse) => {
				// @ts-ignore
				let status = response.data.status
				// @ts-ignore
				let users = response.data.users

				setStatusData(Object.keys(status).map((key) => {
					return status[key];
				}))
				setUsersData(Object.keys(users).map((key) => {
					return users[key];
				}))

				setIsLoading(false)
			})
			.catch(exception => {
				console.log(exception);
			});
	}, []);

	if (isLoading) {
		return 'lol'
	}

	else {

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

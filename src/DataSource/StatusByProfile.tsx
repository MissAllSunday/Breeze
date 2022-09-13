import {ServerStatusResponse, getByProfile} from "../api/StatusApi";
import React, { useState, useEffect } from 'react';
import Status from "../components/Status";
import { statusType } from 'breezeTypes';

export default function  StatusByProfile(): any {
	const [statusData, setStatusData] = useState([] as Array<statusType>);
	const [usersData, setUsersData] = useState([] as any);
	const [isLoading, setIsLoading] = useState(true);

	useEffect(() => {
		getByProfile()
			.then((response:ServerStatusResponse) => {
				setStatusData(response.data.status)
				setUsersData(response.data.users)

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

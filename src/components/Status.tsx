import * as React from "react";
import { statusType } from 'breezeTypes';

interface StatusProps {
	key: number
	status: statusType
	users: {}
	removeStatus(status: statusType): void;
	setNewUsers(user: object): void;
}

interface StatusState {

}

export default class Status extends React.Component<StatusProps, StatusState>
{
	constructor(props: StatusProps) {
		super(props);
	}

	render() {
		return <div />;
	}
}

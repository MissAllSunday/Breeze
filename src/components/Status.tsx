import * as React from "react";

interface StatusProps {
	key: ''
	status: {}
	users: {}
	removeStatus(status: object): void;
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

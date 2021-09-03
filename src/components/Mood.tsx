import * as React from "react";

export default class Mood extends React.Component {
	constructor(props: {
		userMoodId: number,
		userId: number,
		isCurrentUserOwner: boolean,
		canUseMood: boolean
	}) {
		super(props);
	}

	render() {
		return <div/>;
	}
}

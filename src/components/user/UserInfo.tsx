import * as React from 'react';
import Avatar from "./Avatar";
import Mood from "../Mood";
import Utils from "../Utils";


export default class UserInfo extends React.Component
{
	constructor(props: {userData:{}}) {
		super(props);
	}
	render() {
		let moodProps = {
			userMoodId: 1,
			userId: 1,
			isCurrentUserOwner: false,
			canUseMood: false,
			moodTxt: {}
		}
		let avatarUrl = ''

		return <div className="breeze_summary floatleft">
			<div className="roundframe flow_auto">
				<Avatar
					url={avatarUrl} />
				<h3 className="breeze_name">
					memberOnline
					member name color
				</h3>
				<p className="breeze_title">
					primary/post group
				</p>
				<p className="breeze_title">
					group icons
				</p>
				<p className="breeze_description">
					blurb
				</p>
				<p className="breeze_mood">
					<Mood {...moodProps} />
			</p>
		</div>
	</div>;
	}
}

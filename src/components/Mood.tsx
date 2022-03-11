import React, {Component} from 'react';
import { moodType, MoodState, MoodProps } from 'breezeTypes';
import SMF from "../DataSource/SMF";

export default class Mood extends Component<MoodProps, MoodState> {

	constructor(props: MoodProps) {


		super(props);

		this.state = {
			currentMood: this.props.mood,
			isShowing: false
		}
	}

	handleMoodModification(){
		let smfVars = SMF

		if (smfVars.isCurrentUserOwner && smfVars.useMood)
		{
			return <span onClick={this.showMoodList} title="moodLabel" className="pointer_cursor">
				this.props.moodTxt.defaultLabel</span>
		} else {
			return this.state.currentMood.emoji
		}
	}

	showMoodList(){
		this.setState(
			(prevState) => {
				return {
					isShowing: true
				};
			},
			() => console.log("isShowing", this.state.isShowing)
		);
	}
	onChangeMood(mood: moodType){
		this.setState(
			(prevState) => {
				return {
					currentMood: mood
				};
			},
			() => console.log("call the server to save the change", this.state.currentMood)
		);
	}

	render() {
		return <div>

			{this.state.currentMood.emoji }
	</div>;
	}
}

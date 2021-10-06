import React, {Component} from 'react';

type mood = {
	id: number
	description: string
	emoji: string
};

interface MoodProps {
	userMoodId: number,
	userId: number,
	isCurrentUserOwner: boolean,
	canUseMood: boolean,
	moodTxt: {
		defaultLabel: ''
	}
}

interface MoodState {
	currentMood: mood,
	showModal: boolean,
	activeMoods: mood[]
}

export default class Mood extends Component<MoodProps, MoodState> {

	constructor(props: MoodProps) {
		super(props);
	}

	handleMoodModification(){
			if (this.props.isCurrentUserOwner && this.props.canUseMood)
			{
				return <span onClick={this.showMoodList} title="moodLabel" className="pointer_cursor">
					this.props.moodTxt.defaultLabel</span>
			} else {
				return this.state.currentMood.emoji
			}
	}

	showMoodList(){

	}
	onChangeMood(mood: object){
		console.log(mood)

		return mood
	}

	modalBody() {
		let listActiveMoods = this.state.activeMoods.map((mood: mood) =>
			<li
				key={mood.id}
				title={mood.description}
				onClick={() => this.onChangeMood(mood)}
			/>
		)

		return <ul className="set_mood">
			{listActiveMoods}
		</ul>
	}

	handleShowModal(){
		if (!this.state.showModal) {
			return;
		}

		return ''
		// <Modal
		// 	close={this.closeModal()}
		// 	header={this.props.moodTxt.defaultLabel}
		// 	body={this.modalBody()}
		// />

	}

	closeModal() {
		this.setState({showModal: false})

		return this.state.showModal
	}

	render() {
		return <div id="moodList">
			{this.handleMoodModification()}
			{this.handleShowModal()}
	</div>;
	}
}

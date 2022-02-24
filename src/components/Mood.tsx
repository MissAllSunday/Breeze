import React, {Component} from 'react';
import ActiveMoods from "../DataSource/ActiveMoods";
import { moodType, MoodState, MoodProps } from 'breezeTypes';

export default class Mood extends Component<MoodProps, MoodState> {

	constructor(props: MoodProps) {
		// let activeMoods = ActiveMoods()

		super(props);

		this.state = {
			currentMood: {
				id: 0,
				emoji: '',
				body: '',
				description: '',
				isActive: false
			},
			showModal: false,
			// activeMoods: ActiveMoods()
		}
	}

	componentDidMount() {

	}

	handleMoodModification(){
			if (this.props.isCurrentUserOwner && this.props.canUseMood)
			{
				return <span onClick={this.showMoodList} title="moodLabel" className="pointer_cursor">
					this.props.moodTxt.defaultLabel</span>
			} else {
				// return this.state.currentMood.emoji
			}
	}

	showMoodList(){

	}
	onChangeMood(mood: object){
		console.log(mood)

		return mood
	}

	modalBody() {
		let listActiveMoods = ActiveMoods().map((mood: moodType) =>
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
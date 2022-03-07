import {Component} from "react";
import { CommentProps, CommentState } from 'breezeTypes';
import { moodType } from 'breezeTypes';
import Utils from "../Utils";
import SMF from "../DataSource/SMF";

export default class Comment extends Component<CommentProps, CommentState> {

	constructor(props: CommentProps) {
		super(props);
	}


	handleMoodModification(){
		let smfVars = SMF
			if (smfVars.isCurrentUserOwner && smfVars.useMood)
			{
				return
					<span onClick={this.showMoodList} title="moodLabel" className="pointer_cursor">
						this.props.moodTxt.defaultLabel
					</span>
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
		let listActiveMoods = this.state.activeMoods.map((mood: moodType) =>
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

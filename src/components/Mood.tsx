import React, {Component} from 'react';

interface IMood {
	userMoodId: number,
	userId: number,
	isCurrentUserOwner: boolean,
	canUseMood: boolean,
	moodTxt: {
		defaultLabel: ''
	}
}

interface IMoodState {
	currentMood: object
}

export default class Mood extends Component<IMood, IMoodState> {

	private currentMood: object | undefined;

	constructor(props: IMood | Readonly<IMood>) {
		super(props);
	}


	handleMoodModification(){
			if (this.props.isCurrentUserOwner && this.props.canUseMood)
			{
				return <span onClick={this.showMoodList} title="moodLabel" className="pointer_cursor">
					this.props.moodTxt.defaultLabel</span>
			} else {
				return this.currentMood.emoji
			}
	}

	showMoodList(){

	}

	render() {
		return <div id="moodList">
			{this.props.isCurrentUserOwner && this.props.useMood === true
			<span onClick={this.showMoodList} title="moodLabel" class="pointer_cursor">
			{ moodTxt.defaultLabel } { currentMood.emoji }
		</span>}

		<span v-else title="moodLabel">{{ moodTxt.defaultLabel }} {{ currentMood.emoji }}</span>
		<modal v-if="showModal" @close="showModal = false" @click.stop>
	<div slot="header">
			{{ moodTxt.defaultLabel }}
	</div>
		<div slot="body">
			<ul class="set_mood">
				<li
					v-for="mood in activeMoods"
				:key="mood.id"
				title="mood.description"
				@click="changeMood(mood)">
				<span>
							{{ currentMood.emoji }}
						</span>
			</li>
		</ul>
	</div>
	</modal>
	</div>;
	}
}

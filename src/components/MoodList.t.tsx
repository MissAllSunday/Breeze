import React, {Component} from "react";
import {MoodApi} from "../api/MoodApi";
import { moodType } from 'breezeTypes';
import Mood from "./Mood";
import Emoji from "./Emoji";

type Props = {}
type State = {
	list: JSX.Element[]
}

export default class MoodList extends Component<Props, State> {

	constructor(props: {}) {
		super(props)
		this.state = {
			list: []
		}
	}

	saveMood(moodId: number) {

	}

	componentDidMount() {
		let listActiveMoods: any = MoodApi.getActiveMoods()
		let moods = []

		listActiveMoods.then( (result: any) => {
			moods = Object.keys(result.data).map((value: string) => {
				let mood: moodType = result.data[value]

				return <li><Emoji key={mood.id} label={mood.description} codePoint={mood.emoji} handleClick={this.saveMood}></ Emoji></li>
			})
			this.setState({list: moods});
		});
	}

	render() {
		return <div id="moodList">
			<ul>{this.state.list}</ul>
		</div>;
	}
}

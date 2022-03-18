import React, {Component} from "react";
import ActiveMoods from "../DataSource/ActiveMoods";
import { moodType } from 'breezeTypes';
import Mood from "./Mood";

export default class MoodList extends Component {

	constructor(props: {}) {
		super(props)
		this.state = {
			list: []
		};
	}

	handleList() {
		const listActiveMoods = ActiveMoods()

		listActiveMoods.then((response: any) => {
			let moods: moodType[] = Object.values(response.data)

			moods.map((mood: moodType) => {
				return <li><Mood mood={mood}/></li>
			})

			return moods

		})
window.console.log(listActiveMoods)
		return <ul className="set_mood">
			{listActiveMoods}
		</ul>
	}

	render() {
		return <div id="moodList">
			{this.handleList()}
		</div>;
	}
}

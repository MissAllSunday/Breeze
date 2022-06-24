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
		const listActiveMoods: any = ActiveMoods()

		listActiveMoods.then((response: any) => {
			window.console.log(response)
		})

		return ''
		// let moods:any = Object.values(listActiveMoods.data).map((mood: any) => {
		// 	window.console.log(mood)
		// 	return <li key={mood.id}><Mood mood={mood}/></li>
		// })

		// moods = listActiveMoods.then((response: any) => {
		// 	let moods: moodType[] = Object.values(response.data)
		//
		// 	moods.map((mood: moodType) => {
		// 		return <li><Mood mood={mood}/></li>
		// 	})
		// 	window.console.log(moods)
		// 	return moods
		//
		// })

		// return <ul className="set_mood">
		// 	{moods}
		// </ul>
	}

	render() {
		return <div id="moodList">
			{this.handleList()}
		</div>;
	}
}

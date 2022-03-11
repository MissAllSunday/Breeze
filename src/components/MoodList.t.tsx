import React, {Component} from "react";
import Modal from "./modal/Modal";
import ActiveMoods from "../DataSource/ActiveMoods";
import { moodType } from 'breezeTypes';
import { MoodListState } from 'breezeTypes';

export default class Mood extends Component<MoodListState> {

	constructor(props: MoodListState) {


		super(props);

		this.state = {
			isShowing: false
		}
	}

	handleList() {
		const listActiveMoods = ActiveMoods()

		let res = listActiveMoods.then((response: moodType[] | void) => {
			window.console.log(response)
			return response

			// map((mood: moodType) => {
			// 	return <li><Mood mood={mood}/></li>
			//
			// }

		})

		return <ul className="set_mood">
			{res}
		</ul>
	}

	render() {
		return <div id="moodList">
			<Modal
				isShowing={false}
				header='header'
				body={this.handleList()}
			/>
			{this.handleList()}
		</div>;
	}
}

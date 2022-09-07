import React from 'react';
import { moodType} from 'breezeTypes';

const Emoji = (props: {mood: moodType; handleClick: Function }) => (
	<span
		className="emoji"
		role="img"
		aria-label={props.mood.description ? props.mood.description : ""}
		aria-hidden={props.mood.description ? "false" : "true"}
		onClick={props.handleClick(props.mood)}
	>
	{ String.fromCodePoint(props.mood.emoji) }
	</span>
);
export default Emoji;

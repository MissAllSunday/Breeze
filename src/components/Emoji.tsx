import React from 'react';

const Emoji = (props: {key: number; label: string; codePoint: number; handleClick: Function }) => (
	<span
		className="emoji"
		role="img"
		aria-label={props.label ? props.label : ""}
		aria-hidden={props.label ? "false" : "true"}
		onClick={props.handleClick(props.key)}
	>
	{ String.fromCodePoint(props.codePoint) }
	</span>
);
export default Emoji;

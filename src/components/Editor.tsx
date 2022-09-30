import React, {useRef} from "react";

import {useState} from 'react';

const Editor = (props: { saveContent: (content:string) => void; }) => {

	const [content, setContent] = useState("")

	const handleClick = () => {

		props.saveContent(content)
	};

	return (
		<div>
			<textarea id="content" name="content" onChange={e => setContent(e.target.value)} />

			<button onClick={handleClick}>Save</button>
		</div>
	);
};

export default Editor;

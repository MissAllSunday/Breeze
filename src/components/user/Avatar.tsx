import React from "react";

function Avatar(props: { url: string}) {
	const divStyle = {
		backgroundImage: 'url(' + props.url + ')',
	};

	return <div
			className='breeze_avatar avatar_status floatleft'
			style={divStyle}
	/>;
}

export default Avatar

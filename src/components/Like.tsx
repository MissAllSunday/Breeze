import React from "react";

export default class Like extends React.Component {
	handleLike() {

	}

	render() {
		return <div className="smflikebutton">
	<a onClick={this.handleLike} className="msg_like" href='/'>
		<span className='likeClass' />
		handle like
	</a>
	<div className="like_count smalltext">
		lol
	</div>
</div>
	}
}

import React from "react";
import {LikeState, LikeProps } from 'breezeTypes';

export default class Like extends React.Component<LikeProps, LikeState> {
	handleLike() {

	}

	render() {
		return <div className="smflikebutton">
	<a onClick={this.handleLike} className="msg_like" href='/'>
		<span className='likeClass' />
		handle like
	</a>
	<div className="like_count smalltext">
		{this.props.item.count}
	</div>
</div>
	}
}

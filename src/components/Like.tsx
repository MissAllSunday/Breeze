import React from "react";
import {ServerLikeResponse, postLike} from "../api/LikeApi";
import {likeType } from 'breezeTypes';

export default class Like extends React.Component<any> {
	constructor(props: likeType) {
		super(props);
		this.state = this.props.item
	}

	handleLike() {
		postLike(this.state).then((response:ServerLikeResponse) => {
			this.setState(response.data);
		});
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

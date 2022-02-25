import React from "react";
import {LikeState, LikeProps, likeType } from 'breezeTypes';
import likeAction from "../DataSource/LikeAction";
import Utils from "../Utils";

export default class Like extends React.Component<LikeProps, LikeState> {
	constructor(props: likeType) {
		super(props);
		this.state = this.props.item
	}

	handleLike() {
		let baseUrl = Utils.buildBaseUrlWithParams()
		baseUrl.searchParams.append('action', 'like')
		baseUrl.searchParams.append('sa','like')

		try {
			Utils.api().post(baseUrl.href, this.state).then(
				let response
			)
		} catch (err) {

			console.error(err);
		}



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

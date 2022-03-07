import React from "react";
import {likeType } from 'breezeTypes';
import Utils from "../Utils";

export default class Like extends React.Component<any> {
	constructor(props: likeType) {
		super(props);
		this.state = this.props.item
	}

	handleLike() {
		let baseUrl = Utils.buildBaseUrlWithParams()
		baseUrl.searchParams.append('action', 'like')
		baseUrl.searchParams.append('sa','like')

		Utils.api().post(baseUrl.href, this.state).then(data => {
			// @ts-ignore
			this.setState(data.content);
		})
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

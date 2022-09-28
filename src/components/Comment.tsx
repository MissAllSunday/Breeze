import React, {Component} from "react";
import { CommentProps, CommentState } from 'breezeTypes';
import Like from "./Like";

export default class Comment extends Component<CommentProps, CommentState> {

	constructor(props: CommentProps) {
		super(props);
		this.state = {
			comment: this.props.comment
		}
	}


	render() {
		return <div className="comment">
			{this.state.comment.body}
			<Like
				item={this.state.comment.likesInfo}
			/>
	</div>;
	}
}

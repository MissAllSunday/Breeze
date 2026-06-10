import type { CommentProps } from "breezeTypesComments";
import React, { useCallback, useContext, useState } from "react";

import { PermissionsContext } from "../context/PermissionsContext";
import ActionBar from "./actions/ActionBar";
import { commentActionRegistry } from "./actions/actionRegistry";
import { LikeInfo } from "./LikeInfo";
import Avatar from "./user/Avatar";

function Comment(props: CommentProps): React.ReactElement {
	const [classType] = useState(props.comment.isNew ? "fadeIn" : "");
	const [likesInfo, setLikesInfo] = useState(props.comment.likesInfo);
	const timeStamp = props.comment.created_at;
	const permissions = useContext(PermissionsContext);

	// confirm + permission gate live in comment/DeleteAction; this callback stays pure
	const removeComment = useCallback(() => {
		props.removeComment(props.comment);
	}, [props]);

	return (
		<div
			className={`${classType} comment windowbg`}
			id={`comment-${props.comment.id.toString()}`}
		>
			<div className="avatar_compact">
				<Avatar
					href={props.comment.userData.avatar.url}
					userName={props.comment.userData.username}
				/>
				<span
					dangerouslySetInnerHTML={{
						__html: props.comment.userData.link_color,
					}}
				/>
			</div>
			<div
				className="comment_compact content"
				dangerouslySetInnerHTML={{ __html: props.comment.body }}
			/>
			<div className="breeze_meta_bar">
				{permissions.isEnable.enableLikes && permissions.Forum.likesLike && (
					<LikeInfo likeInfo={likesInfo} />
				)}
				<span
					dangerouslySetInnerHTML={{ __html: timeStamp }}
					className={"time_stamp"}
				/>
			</div>
			<ActionBar
				registry={commentActionRegistry}
				baseContext={{
					comment: { ...props.comment, likesInfo },
					permissions,
					removeComment,
					updateLikesInfo: setLikesInfo,
				}}
			/>
		</div>
	);
}

export default React.memo(Comment);

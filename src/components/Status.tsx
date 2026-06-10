import type { CommentListType, CommentType } from "breezeTypesComments";
import type { StatusProps } from "breezeTypesStatus";
import * as React from "react";
import { type Ref, useCallback, useContext, useState } from "react";

import { deleteComment } from "../api/Comment/Delete";
import { postComment } from "../api/Comment/Post";
import { PermissionsContext } from "../context/PermissionsContext";
import ActionBar from "./actions/ActionBar";
import { statusActionRegistry } from "./actions/actionRegistry";
import Comment from "./Comment";
import { LikeInfo } from "./LikeInfo";
import Loading from "./Loading";
import UserInfo from "./user/UserInfo";

function Status(props: StatusProps): React.ReactElement {
	const [classType] = useState(props.status.isNew ? "fadeIn" : "");
	const timeStamp = props.status.created_at;

	const [likesInfo, setLikesInfo] = useState(props.status.likesInfo);
	const [commentsList, setCommentsList] = useState<CommentListType>(
		props.status.comments,
	);
	const [isLoading, setIsLoading] = useState(false);
	const permissions = useContext(PermissionsContext);

	const ref = React.useRef<null | HTMLDivElement>(null);

	React.useLayoutEffect(() => {
		const node: HTMLDivElement | null = ref.current;

		if (node && props.status.isNew) {
			node.scrollIntoView({ behavior: "smooth" });
		}
	});

	// confirm + permission gate live in DeleteAction; this callback stays pure
	const removeStatus = useCallback(() => {
		props.removeStatus(props.status);
	}, [props]);

	const createComment = useCallback(
		(content: string, mentionIds?: number[]): boolean => {
			if (!permissions.Comments.post) {
				return false;
			}

			setIsLoading(true);

			postComment({
				status_id: props.status.id,
				body: content,
				mention_ids: mentionIds ?? [],
			})
				.then((newComments: CommentListType | undefined) => {
					if (!newComments) {
						return;
					}

					setCommentsList((prevCommentsList: CommentListType) => [
						...prevCommentsList,
						...newComments,
					]);
				})
				.finally(() => {
					setIsLoading(false);
				});

			return true;
		},
		[props.status.id, permissions.Comments.post],
	);

	const removeComment = useCallback((comment: CommentType) => {
		setIsLoading(true);
		deleteComment(comment.id)
			.then((deleted) => {
				if (deleted) {
					setCommentsList((prevCommentsList: CommentListType) =>
						prevCommentsList.filter(
							(currentComment: CommentType) => currentComment.id !== comment.id,
						),
					);
				}
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, []);

	return (
		<li
			className={`${classType} status`}
			key={props.status.id}
			id={`status-${props.status.id.toString()}`}
			ref={ref as Ref<HTMLLIElement>}
		>
			{isLoading ? <Loading /> : ""}
			<div className="post_wrapper">
				<div className="poster">
					<UserInfo userData={props.status.userData} />
				</div>
				<div className="postarea">
					<div className="windowbg">
						<div
							className="content"
							dangerouslySetInnerHTML={{ __html: props.status.body }}
						/>
						<div className="breeze_meta_bar">
							{permissions.isEnable.enableLikes &&
								permissions.Forum.likesLike && (
									<LikeInfo likeInfo={likesInfo} />
								)}
							<span
								dangerouslySetInnerHTML={{ __html: timeStamp }}
								className={"time_stamp"}
							/>
						</div>
						<ul className="status">
							{commentsList.map((comment: CommentType) => (
								<Comment
									key={comment.id}
									comment={comment}
									removeComment={removeComment}
								/>
							))}
						</ul>
						<ActionBar
							registry={statusActionRegistry}
							baseContext={{
								status: { ...props.status, likesInfo },
								permissions,
								createComment,
								removeStatus,
								updateLikesInfo: setLikesInfo,
							}}
						/>
					</div>
				</div>
			</div>
		</li>
	);
}

export default React.memo(Status);

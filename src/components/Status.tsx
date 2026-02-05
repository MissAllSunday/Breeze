import type { CommentListType, CommentType } from "breezeTypesComments";
import type { StatusProps } from "breezeTypesStatus";
import * as React from "react";
import {type Ref, useCallback, useContext, useState} from "react";

import { deleteComment } from "../api/Comment/Delete";
import { postComment } from "../api/Comment/Post";
import { PermissionsContext } from "../context/PermissionsContext";
import smfVars from "../DataSource/SMF";
import smfTextVars from "../DataSource/Txt";
import Comment from "./Comment";
import Editor from "./Editor";
import { Like } from "./Like";
import Loading from "./Loading";
import Avatar from "./user/Avatar";
import UserInfo from "./user/UserInfo";

function Status(props: StatusProps): React.ReactElement {
	const [classType] = useState(props.status.isNew ? "fadeIn" : "");
	const timeStamp = props.status.created_at;

	const [commentsList, setCommentsList] = useState<CommentListType>(
		Object.values(props.status.comments),
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

	const removeStatus = useCallback(() => {
		if (!window.confirm(smfVars.youSure) || !permissions.Status.delete) {
			return;
		}

		props.removeStatus(props.status);
	}, [permissions.Status.delete, props]);

	const createComment = useCallback(
		(content: string): boolean => {
			if (!permissions.Comments.post) {
				return false;
			}

			setIsLoading(true);

			postComment({
				status_id: props.status.id,
				body: content,
			})
				.then((newComments: CommentListType) => {
					setCommentsList((prevCommentsList) => [...prevCommentsList, ...Object.values(newComments)]);
				})
				.finally(() => {
					setIsLoading(false);
				});

			return true;
		},
		[props.status.id, permissions.Comments.post],
	);

	const removeComment = useCallback(
		(comment: CommentType) => {
			setIsLoading(true);
			deleteComment(comment.id)
				.then((deleted) => {
					if (deleted) {
						setCommentsList((prevCommentsList) =>
							prevCommentsList.filter(
								(currentComment: CommentType) =>
									currentComment.id !== comment.id,
							),
						);
					}
				})
				.finally(() => {
					setIsLoading(false);
				});
		},
		[],
	);

	return (
		<li
			className={`${classType} status`}
			key={props.status.id}
			id={`status-${props.status.id.toString()}`}
			ref={ref as Ref<HTMLLIElement>}
		>
			{isLoading ? <Loading /> : ""}
			<div className="floatleft userinfo">
				<UserInfo userData={props.status.userData} />
			</div>
			<div className="windowbg floatright">
				<div
					className="content"
					dangerouslySetInnerHTML={{ __html: props.status.body }}
				/>
				<div className="half_content">
					<Like likeInfo={props.status.likesInfo} />
				</div>
				<div className="half_content">
					<div className={"info_bar"}>
						<span
							dangerouslySetInnerHTML={{ __html: timeStamp }}
							className={"time_stamp"}
						/>
						{permissions.Status.delete && (
							<span
								role="button"
								tabIndex={0}
								className="main_icons remove_button pointer_cursor"
								title={smfTextVars.general.delete}
								onClick={removeStatus}
								data-testid="deleteStatus"
							/>
						)}
					</div>
				</div>
				<hr />
				<ul className="status">
					{commentsList.map((comment: CommentType) => (
						<Comment
							key={comment.id}
							comment={comment}
							removeComment={removeComment}
						/>
					))}
				</ul>
				<div className="comment_posting">
					{permissions.Comments.post ? (
						<>
							<Avatar
								href={smfVars.currentUserAvatar}
								userName={""}
								customClassName={"comment_avatar"}
							/>
							<Editor saveContent={createComment} isFull={false} />
						</>
					) : (
						""
					)}
				</div>
			</div>
		</li>
	);
}

export default React.memo(Status);

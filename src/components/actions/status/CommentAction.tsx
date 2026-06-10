import type { StatusAction, StatusActionContext } from "breezeTypesActions";
import type React from "react";
import { useCallback } from "react";

import smfVars from "../../../DataSource/SMF";
import smfTextVars from "../../../DataSource/Txt";
import Editor from "../../Editor";
import Avatar from "../../user/Avatar";

const CommentPanel: React.FC<StatusActionContext> = ({
	createComment,
	closePanel,
}: StatusActionContext) => {
	const handleSave = useCallback(
		(content: string, mentionIds?: number[]): boolean => {
			const saved = createComment(content, mentionIds);
			if (saved) {
				closePanel();
			}
			return saved;
		},
		[createComment, closePanel],
	);

	return (
		<div className="comment_posting" data-testid="commentPanel">
			<Avatar
				href={smfVars.currentUserAvatar}
				userName=""
				customClassName="comment_avatar"
			/>
			<Editor saveContent={handleSave} isFull={false} />
		</div>
	);
};

const CommentAction: StatusAction = {
	id: "comment",
	label: smfTextVars.actions.comment,
	icon: "reply_button",
	order: 20,
	isVisible: ({ permissions }: StatusActionContext): boolean =>
		permissions.Comments.post,
	Panel: CommentPanel,
};

export default CommentAction;

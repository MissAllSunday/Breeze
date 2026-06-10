import type { CommentAction, CommentActionContext } from "breezeTypesActions";

import { postLike } from "../../../api/Like/Post";
import smfVars from "../../../DataSource/SMF";
import smfTextVars from "../../../DataSource/Txt";

const LikeAction: CommentAction = {
	id: "like",
	label: ({ comment }: CommentActionContext): string =>
		comment.likesInfo.alreadyLiked ? smfTextVars.like.unlike : smfTextVars.actions.like,
	icon: ({ comment }: CommentActionContext): string =>
		comment.likesInfo.alreadyLiked ? "unlike" : "like",
	order: 10,
	isVisible: ({ permissions }: CommentActionContext): boolean =>
		permissions.isEnable.enableLikes && permissions.Forum.likesLike,
	onClick: ({ comment, updateLikesInfo }: CommentActionContext): void => {
		if (smfVars.confirmPost && !window.confirm(smfVars.youSure)) {
			return;
		}
		void postLike(comment.likesInfo).then(updateLikesInfo);
	},
};

export default LikeAction;

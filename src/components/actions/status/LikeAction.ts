import type { StatusAction, StatusActionContext } from "breezeTypesActions";

import { postLike } from "../../../api/Like/Post";
import smfVars from "../../../DataSource/SMF";
import smfTextVars from "../../../DataSource/Txt";

const LikeAction: StatusAction = {
	id: "like",
	label: ({ status }: StatusActionContext): string =>
		status.likesInfo.alreadyLiked ? smfTextVars.like.unlike : smfTextVars.actions.like,
	icon: ({ status }: StatusActionContext): string =>
		status.likesInfo.alreadyLiked ? "unlike" : "like",
	order: 10,
	isVisible: ({ permissions }: StatusActionContext): boolean =>
		permissions.isEnable.enableLikes && permissions.Forum.likesLike,
	onClick: ({ status, updateLikesInfo }: StatusActionContext): void => {
		if (smfVars.confirmPost && !window.confirm(smfVars.youSure)) {
			return;
		}
		void postLike(status.likesInfo).then(updateLikesInfo);
	},
};

export default LikeAction;

import type { CommentAction, CommentActionContext } from "breezeTypesActions";

import smfVars from "../../../DataSource/SMF";
import smfTextVars from "../../../DataSource/Txt";

const DeleteAction: CommentAction = {
	id: "delete",
	label: smfTextVars.actions.delete,
	icon: "remove_button",
	order: 20,
	testId: "deleteComment",
	isVisible: ({ permissions }: CommentActionContext): boolean =>
		permissions.Comments.delete,
	onClick: ({ removeComment }: CommentActionContext): void => {
		if (!window.confirm(smfVars.youSure)) {
			return;
		}
		removeComment();
	},
};

export default DeleteAction;

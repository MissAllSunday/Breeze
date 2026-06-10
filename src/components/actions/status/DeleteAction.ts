import type { StatusAction, StatusActionContext } from "breezeTypesActions";

import smfVars from "../../../DataSource/SMF";
import smfTextVars from "../../../DataSource/Txt";

const DeleteAction: StatusAction = {
	id: "delete",
	label: smfTextVars.actions.delete,
	icon: "remove_button",
	order: 30,
	testId: "deleteStatus",
	isVisible: ({ permissions }: StatusActionContext): boolean =>
		permissions.Status.delete,
	onClick: ({ removeStatus }: StatusActionContext): void => {
		if (!window.confirm(smfVars.youSure)) {
			return;
		}
		removeStatus();
	},
};

export default DeleteAction;

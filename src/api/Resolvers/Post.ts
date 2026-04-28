import type { CommentListType } from "breezeTypesComments";
import type { LikeType } from "breezeTypesLikes";
import type { StatusListType } from "breezeTypesStatus";
import smfTextVars from "../../DataSource/Txt";
import { showError, showInfo } from "../../utils/tooltip";
import { updateCsrfToken } from "../Base";

export const resolvePost = async (
	response: Response,
): Promise<StatusListType | CommentListType | LikeType | undefined> => {
	const { content, message, token } = await response.json();

	if (token) {
		updateCsrfToken(token);
	}

	if (response.ok && response.status === 201) {
		showInfo(message);

		return content;
	}

	showError(message ?? smfTextVars.error.generic);
};

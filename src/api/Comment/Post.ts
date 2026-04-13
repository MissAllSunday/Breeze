import type { CommentListType } from "breezeTypesComments";

import smfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import { showError } from "../../utils/tooltip";
import { baseConfig, baseUrl } from "../Base";
import { resolvePost } from "../Resolvers/Post";

export const postComment = async (
	commentParams: object,
): Promise<CommentListType> => {
	try {
		const postCommentResults = await fetch(
			baseUrl("breezeComment", "postComment"),
			{
				method: "POST",
				body: JSON.stringify(
					baseConfig({
						...commentParams,
						user_id: smfVars.user_id,
					}),
				),
			},
		);

		return await resolvePost(postCommentResults);
	} catch (_error: unknown) {
		showError(smfTextVars.error.generic);
	}
};

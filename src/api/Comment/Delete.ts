import smfTextVars from "../../DataSource/Txt";
import { baseConfig, baseUrl } from "../Base";
import { resolveDelete } from "../Resolvers/Delete";

export const deleteComment = async (commentId: number): Promise<boolean> => {
	const deleteCommentResults = await fetch(
		baseUrl("breezeComment", "deleteComment"),
		{
			method: "POST",
			body: JSON.stringify(
				baseConfig({
					id: commentId,
				}),
			),
		},
	);

	return resolveDelete(
		deleteCommentResults,
		smfTextVars.general.deletedComment,
	);
};

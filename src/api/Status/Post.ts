import type { StatusListType } from "breezeTypesStatus";

import smfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import { showError } from "../../utils/tooltip";
import { baseConfig, baseUrl } from "../Base";
import { resolvePost } from "../Resolvers/Post";

export const postStatus = async (
	content: string,
	mentionIds: number[] = [],
): Promise<StatusListType | undefined> => {
	try {
		const response = await fetch(baseUrl("breezeStatus", "postStatus"), {
			method: "POST",
			body: JSON.stringify(
				baseConfig({
					wall_id: smfVars.wall_id,
					user_id: smfVars.user_id,
					body: content,
					mention_ids: mentionIds,
				}),
			),
		});

		return (await resolvePost(response)) as StatusListType | undefined;
	} catch (_error: unknown) {
		showError(smfTextVars.error.generic);
	}
};

import type { LikeInfoType } from "breezeTypesLikes";

import SmfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import { showError } from "../../utils/tooltip";
import { baseConfig, baseUrl } from "../Base";
import { resolvePost } from "../Resolvers/Post";

export interface IPostLikeParams {
	id_member: number;
	content_type: string;
	content_id: number;
}

export const postLike = async (
	likeInfo: LikeInfoType,
): Promise<LikeInfoType> => {
	try {
		const params: IPostLikeParams = {
			id_member: SmfVars.user_id,
			content_type: likeInfo.type,
			content_id: likeInfo.contentId,
		};

		const likeResults = await fetch(baseUrl("breezeLike", "like"), {
			method: "POST",
			body: JSON.stringify(baseConfig(params)),
		});

		return await resolvePost(likeResults);
	} catch (_error: unknown) {
		showError(smfTextVars.error.generic);
		throw _error;
	}
};

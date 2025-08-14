import type { LikeInfoType, LikeProps } from "breezeTypesLikes";
import type React from "react";
import { useCallback, useContext, useState } from "react";

import { postLike } from "../api/Like/Post";
import { PermissionsContext } from "../context/PermissionsContext";
import smfVars from "../DataSource/SMF";
import smfTextVars from "../DataSource/Txt";
import { LikeInfo } from "./LikeInfo";
import Loading from "./Loading";

export const Like: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
	const [likeInfo, setLikeInfo] = useState<LikeInfoType>(props.likeInfo);
	const permissions = useContext(PermissionsContext);
	const [isLoading, setIsLoading] = useState(false);

	const handleLike = useCallback(() => {
		if (!window.confirm(smfVars.youSure)) {
			return;
		}
		setIsLoading(true);

		postLike(likeInfo)
			.then((newLikeInfo: LikeInfoType) => {
				setLikeInfo(newLikeInfo);
			})
			.finally(() => setIsLoading(false));
	}, [likeInfo]);

	const title = likeInfo?.alreadyLiked
		? smfTextVars.like.unlike
		: smfTextVars.like.like;
	const emoji = likeInfo?.alreadyLiked
		? String.fromCodePoint(128078)
		: String.fromCodePoint(128077);

	return permissions.isEnable.enableLikes && permissions.Forum.likesLike ? (
		<div className="smflikebutton">
			{isLoading ? <Loading /> : ""}
			<a
				href="#"
				tabIndex={likeInfo?.contentId ?? 0}
				onClick={handleLike}
				className="breeze_anchor pointer_cursor"
				title={title}
			>
				{emoji}
			</a>{" "}
			{likeInfo && <LikeInfo likeInfo={likeInfo} />}
		</div>
	) : null;
};

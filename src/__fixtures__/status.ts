import type { StatusType, IFetchStatus } from "breezeTypesStatus";

import { comments } from "./comments";
import likes from "./likes";
import permissions from "./permissions";
import { userData } from "./userData";

const basic: StatusType = {
	id: 666,
	wall_id: 1,
	user_id: 666,
	likes: 0,
	body: "this is a status body content",
	created_at: "some date",
	likesInfo: likes.basic,
	comments: [comments.basic],
	userData: userData.basic,
	isNew: true,
};

const custom = (replace: Partial<StatusType>) => {
	return { ...basic, ...replace };
};

const fetchStatus: IFetchStatus = {
	data: [basic],
	permissions: permissions.basic,
	pagination: {
		nextCursor: null,
		hasMore: false,
	},
	total: 1,
};

const customFetchStatus = (replace: Partial<IFetchStatus>): IFetchStatus => {
	return { ...fetchStatus, ...replace };
};

export const status = { basic, custom, fetchStatus, customFetchStatus };

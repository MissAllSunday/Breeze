declare module "breezeTypesStatus" {
	interface StatusType {
		id: number;
		wall_id: number;
		user_id: number;
		likes: number;
		body: string;
		created_at: string;
		likesInfo: LikeInfoType;
		comments: CommentType[];
		userData: UserDataType;
		isNew: boolean;
	}

	interface IFetchStatus {
		data: StatusListType;
		permissions: PermissionsContextType;
		pagination: {
			nextCursor: string | null;
			hasMore: boolean;
		};
		total: number;
	}

	type StatusListType = StatusType[];

	interface StatusListProps {
		statusList: StatusListType;
	}

	interface StatusProps {
		status: StatusType;
		removeStatus: function;
	}

	interface StatusState {
		isLoading: boolean;
		classType: string;
	}
}

module.exports = {
	StatusListType,
	StatusType,
	StatusProps,
	StatusState,
};

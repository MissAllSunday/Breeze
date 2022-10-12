declare module 'breezeTypes' {
	type statusType = {
		id: number,
		wallId: number,
		userId: number,
		likes: number,
		body: string,
		createdAt: string,
		likesInfo: likeType,
		comments: Array<commentType>,
		userData: userDataType
	}

	type statusListType = Map<statusType>;

	interface StatusListProps {
		statusList: statusListType,
		onRemoveStatus(status: statusType): void;
	}

	interface StatusProps {
		status: statusType
		removeStatus(status: statusType): void;
	}
}

module.exports = {
	statusListType,
	statusType,
	StatusProps,
	StatusState,
};

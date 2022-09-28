declare module 'breezeTypes' {
	type statusType = {
		id: number,
		wallId: number,
		userId: number,
		likes: number,
		body: string,
		createdAt: string,
		likesInfo: likeType
		comments: Array<commentType>
	}

	interface StatusProps {
		status: statusType
		users: {}
		removeStatus(status: statusType): void;
		setNewUsers(user: object): void;
	}

	interface StatusState {

	}
}

module.exports = {
	statusType,
	StatusProps,
	StatusState,
};

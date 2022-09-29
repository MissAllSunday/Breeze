declare module 'breezeTypes' {
	type commentType = {
		id: number
		statusId: number,
		userId: number,
		likes: number,
		body: string,
		likesInfo: likeType,
		createdAt: string,
		userData: userDataType
	};

	type commentList = { [id: number]: commentType };

	interface CommentProps {
		comment: commentType,
		removeComment:function
	}

	interface CommentState {
		comment: commentType
	}
}

module.exports = {
	commentList,
	commentType,
	CommentProps,
	CommentState,
};

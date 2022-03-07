declare module 'breezeTypes' {
	type commentType = {
		id: number
		statusId: number,
		userId: number,
		likes: number,
		body: string,
		createdAt: string
	};

	interface CommentProps {
		comment: commentType,
	}

	interface CommentState {
		comment: commentType,
		currentMood: moodType,
		activeMoods: moodType[],
		showModal: boolean,
	}
}

module.exports = {
	commentType,
	CommentProps,
	CommentState,
};

declare module 'breezeTypes' {
	type commentType = {
		id: number
		body: string
	};

	interface CommentProps {
		id: number,
		body: string,
		isCurrentUserOwner: boolean,
		canUseMood: boolean,
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

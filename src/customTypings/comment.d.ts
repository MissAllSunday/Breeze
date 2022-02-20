declare module 'breezeTypes' {
	type comment = {
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
		comment: comment,
		currentMood: moodType,
		activeMoods: moodType[],
		showModal: boolean,
	}
}

module.exports = {
	comment,
	CommentProps,
	CommentState,
};

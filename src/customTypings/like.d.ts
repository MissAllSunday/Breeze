declare module 'breezeTypes' {
	type likeType = {
		item: number,
	};

	interface MoodProps {
		userMoodId: number,
		isCurrentUserOwner: boolean,
		canUseMood: boolean,
	}

	interface MoodState {
		currentMood: moodType,
		showModal: boolean,
		// activeMoods: moodType[]
	}
}

module.exports = {
	moodType,
	MoodProps,
	MoodState,
};

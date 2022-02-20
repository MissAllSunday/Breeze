declare module 'breezeTypes' {
	type moodType = {
		id: number,
		emoji: string
		body: string
		description: string
		isActive: boolean
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

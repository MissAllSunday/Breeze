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
		userId: number,
		isCurrentUserOwner: boolean,
		canUseMood: boolean,
		moodTxt: Object
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

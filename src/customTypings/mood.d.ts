declare module 'breezeTypes' {
	type moodType = {
		id: number,
		emoji: string
		body: string
		description: string
		isActive: boolean
	};

	interface MoodProps {
		mood: moodType,
	}

	interface MoodState {
		currentMood: moodType,
		isShowing: boolean
	}
}

module.exports = {
	moodType,
	MoodProps,
	MoodState,
};

declare module 'breezeTypes' {
	type mood = {
		id: number,
		emoji: number
		body: string
		description: string
		isActive: boolean
	};

	interface MoodProps {
		id: number,
		emoji: number
		body: string
		description: string
		isActive: boolean
	}

	interface MoodState {

	}
}

module.exports = {
	mood,
	MoodProps,
	MoodState,
};

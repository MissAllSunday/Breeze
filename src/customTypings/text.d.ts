declare module 'breezeTypes' {
	type moodTextType = {
		emoji:string,
		description:string,
		enable:string,
		invalidEmoji:string,
		emptyEmoji:string,
		moodChange:string,
		newMood:string,
		sameMood:string,
		defaultLabel:string
	};

	type generalTextType = {save:string,delete:string,editing:string,close:string,cancel:string,send:string,preview:string,previewing:string,wrongValues:string,errorEmpty:string
	};
	type likeTextType = {
		unlike: string,
		like: string
	};
}

module.exports = {
	moodTextType,
	generalTextType,
	likeTextType,
};

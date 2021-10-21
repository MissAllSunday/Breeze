declare module 'breezeTypes' {
	type comment = {
		id: number
		body: string
	};

	interface CommentProps {

	}

	interface CommentState {
		comment: comment
	}
}

module.exports = {
	comment,
	CommentProps,
	CommentState,
};

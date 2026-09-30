declare module "breezeTypesComments" {
	interface CommentType {
		id: number;
		status_id: number;
		user_id: number;
		likes: number;
		body: string;
		likesInfo: LikeInfoType;
		created_at: string;
		userData: UserDataType;
		isNew: boolean;
		/**
		 * Per-item delete right, resolved server-side from the persisted
		 * `user_id` and the parent status `wall_id`. The wall-level
		 * `permissions.Comments.delete` only means "may delete ANY item".
		 */
		canDelete: boolean;
	}

	type CommentListType = CommentType[];

	interface CommentReducerData {
		type: string;
		comment: CommentType;
	}

	interface NewCommentProps {
		content: string;
		status: StatusType;
	}

	interface RemoveCommentProps {
		status: StatusType;
		comment: CommentType;
	}

	interface CommentListProps {
		CommentList: CommentList;
		statusId: number;
	}

	interface CommentProps {
		comment: CommentType;
		removeComment: function;
	}
	interface CommentState {
		visible: boolean;
		classType: string;
	}
}

module.exports = {
	CommentList,
	CommentType,
	CommentProps,
};

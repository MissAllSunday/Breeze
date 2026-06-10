export type IServerActions = "breezeStatus" | "breezeLike" | "breezeComment";

declare module "breezeTypesActions" {
	interface StatusActionContext {
		status: import("breezeTypesStatus").StatusType;
		permissions: import("breezeTypesPermissions").PermissionsContextType;
		closePanel: () => void;
		/** Posts a new comment on status. Returns true on success (callers may closePanel). */
		createComment: (content: string, mentionIds?: number[]) => boolean;
		/** Deletes the status after user confirmation. */
		removeStatus: () => void;
		/** Pushes an updated LikeInfoType back into the status's local React state. */
		updateLikesInfo: (newLikeInfo: import("breezeTypesLikes").LikeInfoType) => void;
	}

	interface CommentActionContext {
		comment: import("breezeTypesComments").CommentType;
		permissions: import("breezeTypesPermissions").PermissionsContextType;
		closePanel: () => void;
		/** Deletes the comment after user confirmation. */
		removeComment: () => void;
		/** Pushes an updated LikeInfoType back into the comment's local React state. */
		updateLikesInfo: (newLikeInfo: import("breezeTypesLikes").LikeInfoType) => void;
	}

	interface StatusAction {
		id: string;
		/** Static label or a resolver called with the current context on every render. */
		label: string | ((ctx: StatusActionContext) => string);
		/** Static SMF icon class or a resolver called with the current context on every render. */
		icon: string | ((ctx: StatusActionContext) => string);
		order: number;
		/** Optional data-testid forwarded to the action bar button. */
		testId?: string;
		isVisible: (ctx: StatusActionContext) => boolean;
		onClick?: (ctx: StatusActionContext) => void;
		Panel?: import("react").ComponentType<StatusActionContext>;
	}

	interface CommentAction {
		id: string;
		/** Static label or a resolver called with the current context on every render. */
		label: string | ((ctx: CommentActionContext) => string);
		/** Static SMF icon class or a resolver called with the current context on every render. */
		icon: string | ((ctx: CommentActionContext) => string);
		order: number;
		/** Optional data-testid forwarded to the action bar button. */
		testId?: string;
		isVisible: (ctx: CommentActionContext) => boolean;
		onClick?: (ctx: CommentActionContext) => void;
		Panel?: import("react").ComponentType<CommentActionContext>;
	}
}

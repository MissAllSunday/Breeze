declare module "breezeTypesText" {
	interface GeneralTextType {
		deletedStatus: string;
		deletedComment: string;
		save: string;
		delete: string;
		close: string;
		cancel: string;
		send: string;
		end: string;
		loadMore: string;
		goUp: string;
		goBack: string;
		emptyData: string;
		buddyAdd: string;
		buddyRemove: string;
	}

	interface LikeTextType {
		unlike: string;
		like: string;
	}

	interface TabsTextType {
		wall: string;
		about: string;
		activity: string;
		buddies: string;
	}

	interface ErrorTextType {
		wrongValues: string;
		errorEmpty: string;
		noStatus: string;
		generic: string;
	}

	interface ActionsTextType {
		like: string;
		comment: string;
		delete: string;
	}
}

module.exports = {
	TabsTextType,
	GeneralTextType,
	LikeTextType,
	ErrorTextType,
	ActionsTextType,
};

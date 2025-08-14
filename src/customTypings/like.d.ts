declare module "breezeTypesLikes" {
	type LikeInfoType = {
		text: string;
		href: string;
		likes: LikeType[];
		count: number;
		contentId: number;
		alreadyLiked: boolean;
		canLike: boolean;
		type: string;
	};

	interface LikeType {
		userData: UserDataType;
		contentId: number;
		count: number;
		type: string;
		likeTime: string;
	}

	interface LikeProps {
		likeInfo: LikeInfoType;
	}
}

module.exports = {
	UsersLikeInfoType,
	LikeType,
	LikeProps,
	LikeInfoType,
};

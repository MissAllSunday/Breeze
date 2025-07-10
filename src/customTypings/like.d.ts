declare module 'breezeTypesLikes' {

  type UsersLikeInfoType = {
    userData: UserDataType
    likeTime: string
  };
  type AdditionalInfoType = {
    text: string
    href: string
    usersLikeInfo: UsersLikeInfoType[],
  };

  interface LikeType {
    additionalInfo: AdditionalInfoType
    alreadyLiked: boolean
    canLike: boolean
    contentId: number
    count: number
    type: string
  }

  interface LikeProps {
    item: LikeType
  }
}

module.exports = {
  AdditionalInfoType,
  UsersLikeInfoType,
  LikeType,
  LikeProps,
};

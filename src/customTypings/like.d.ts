declare module 'breezeTypesLikes' {
  interface LikeType {
    additionalInfo: {
      text: string
      href: string
      usersData: UserDataType[]
    }
    alreadyLiked: boolean
    canLike: boolean
    contentId: number
    count: number
    type: string
  }

  interface LikeProps {
    item: LikeType
  }
  interface LikeInfoProps {
    item: LikeType
  }
}

module.exports = {
  LikeType,
  LikeProps,
  LikeInfoState,
};

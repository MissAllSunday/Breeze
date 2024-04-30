declare module 'breezeTypesLikes' {
  interface LikeType {
    additionalInfo: {
      text: string
      href: string
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

  interface LikeInfoState {
    profile: UserDataType
    timestamp: string
  }

}

module.exports = {
  LikeType,
  LikeProps,
  LikeInfoState,
};

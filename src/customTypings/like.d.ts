declare module 'breezeTypes' {
  interface likeType {
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
    item: likeType
  }
  interface LikeInfoProps {
    item: likeType
  }

  interface LikeInfoState {
    profile: userDataType
    timestamp: string
  }

}

module.exports = {
  likeType,
  LikeProps,
  LikeInfoState
}

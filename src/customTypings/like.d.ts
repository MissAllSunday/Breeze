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
}

module.exports = {
  likeType,
  LikeProps
}

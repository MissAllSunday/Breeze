declare module 'breezeTypes' {
  interface likeType {
    additionalInfo: string
    alreadyLiked: boolean
    canLike: boolean
    contentId: number
    count: number
    type: string
  }

  interface LikeProps {
	  item: likeType
  }

  interface LikeState {}
}

module.exports = {
  moodType,
  MoodProps,
  MoodState
}

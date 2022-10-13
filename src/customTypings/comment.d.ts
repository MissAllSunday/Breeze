declare module 'breezeTypes' {
  interface commentType {
    id: number
    statusId: number
    userId: number
    likes: number
    body: string
    likesInfo: likeType
    createdAt: string
    userData: userDataType
  }

  interface commentList { [id: number]: commentType }

  interface CommentProps {
	  comment: commentType
	  removeComment: function
  }
}

module.exports = {
  commentList,
  commentType,
  CommentProps
}

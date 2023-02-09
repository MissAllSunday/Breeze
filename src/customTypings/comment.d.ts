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
    isNew: boolean
  }

  type commentList = Map<commentType>

  interface newCommentProps {
    content: string
    status: statusType
  }

  interface removeCommentProps {
    status: statusType
    comment: commentType
  }

  interface CommentListProps {
    commentList: commentList
    removeComment: function
  }

  interface CommentProps {
    comment: commentType
    removeComment: function
  }
  interface CommentState {
    visible: boolean
    classType: string
  }
}

module.exports = {
  commentList,
  commentType,
  CommentProps
}

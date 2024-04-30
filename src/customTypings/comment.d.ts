declare module 'breezeTypesComments' {
  interface CommentType {
    id: number
    statusId: number
    userId: number
    likes: number
    body: string
    likesInfo: LikeType
    createdAt: string
    userData: UserDataType
    isNew: boolean
  }

  type CommentListType = Map<CommentType>;

  interface CommentReducerData {
    type: string
    comment: CommentType
  }

  interface NewCommentProps {
    content: string
    status: StatusType
  }

  interface RemoveCommentProps {
    status: StatusType
    comment: CommentType
  }

  interface CommentListProps {
    CommentList: CommentList
    statusId: number
  }

  interface CommentProps {
    comment: CommentType
    removeComment: function
  }
  interface CommentState {
    visible: boolean
    classType: string
  }
}

module.exports = {
  CommentList,
  CommentType,
  CommentProps,
};

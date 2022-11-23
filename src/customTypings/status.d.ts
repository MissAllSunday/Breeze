declare module 'breezeTypes' {
  interface statusType {
    id: number
    wallId: number
    userId: number
    likes: number
    body: string
    createdAt: string
    likesInfo: likeType
    comments: commentType[]
    userData: userDataType
  }

  type statusListType = Map<statusType>

  interface StatusListProps {
    statusList: statusListType
    onRemoveStatus: (status: statusType) => void
    onRemoveComment: function
    onCreateComment: function
  }

  interface StatusProps {
    status: statusType
    removeStatus: (status: statusType) => void
    removeComment: function
    createComment: function
  }
}

module.exports = {
  statusListType,
  statusType,
  StatusProps,
  StatusState
}

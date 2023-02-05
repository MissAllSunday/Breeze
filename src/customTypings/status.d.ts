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
    removeStatus: (status: statusType) => void
    removeComment: function
    onCreateComment: function
  }

  interface StatusProps {
    status: statusType
    removeStatus: (status: statusType) => void
    removeComment: function
    createComment: function
  }

  interface StatusState {
    isLoading: boolean
    visible: boolean
  }
}

module.exports = {
  statusListType,
  statusType,
  StatusProps,
  StatusState
}

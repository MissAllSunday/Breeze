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
    isNew: boolean
  }

  interface statusReducerData {
    type: string
    status: statusListType
  }

  type statusListType = Map<statusType>

  interface StatusListProps {
    statusList: statusListType
  }

  interface StatusProps {
    status: statusType
    removeStatus: (status: statusType) => void
  }

  interface StatusState {
    isLoading: boolean
    classType: string
  }
}

module.exports = {
  statusListType,
  statusType,
  StatusProps,
  StatusState
}

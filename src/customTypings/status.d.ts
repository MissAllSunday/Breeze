declare module 'breezeTypesStatus' {
  interface StatusType {
    id: number
    wallId: number
    userId: number
    likes: number
    body: string
    createdAt: string
    likesInfo: LikeType
    comments: CommentType[]
    userData: UserDataType
    isNew: boolean
  }

  type StatusDispatchContextType = function;

  interface StatusReducerData {
    type: string
    statusListState: StatusType
  }

  type StatusListType = Map<StatusType>;

  interface StatusListProps {
    statusList: StatusListType
  }

  interface StatusProps {
    status: StatusType
    removeStatus: (status: StatusType) => void
  }

  interface StatusState {
    isLoading: boolean
    classType: string
  }
}

module.exports = {
  StatusListType,
  StatusType,
  StatusProps,
  StatusState,
};

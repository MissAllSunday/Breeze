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

  interface IFetchStatus {
    data: StatusListType,
    permissions: PermissionsContextType,
    total: number
  }

  type StatusListType = Map<StatusType>;

  interface StatusListProps {
    statusList: StatusListType
  }

  interface StatusProps {
    status: StatusType
    removeStatus: function
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

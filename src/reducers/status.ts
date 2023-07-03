import { statusListType, statusReducerData, statusType } from 'breezeTypes'

export default function statusReducer (statusListState: statusListType, action: statusReducerData): statusListType {
  let newState: statusListType

  switch (action.type) {
    case 'create': {
      newState = [...statusListState, action.status]
      break
    }
    case 'delete': {
      newState = statusListState.filter(function (commentListItem: statusType) {
        return commentListItem.id !== action.status.id
      })
      break
    }
    default:
      newState = statusListState
  }

  return newState
}

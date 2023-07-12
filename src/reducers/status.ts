import { statusListType, statusReducerData, statusType } from 'breezeTypes'

export default function statusReducer (statusListState: statusListType, action: statusReducerData): statusListType {
  let newState: statusListType = {}

  switch (action.type) {
    case 'create': {
      if (Object.keys(statusListState).length !== 0) {
        newState = [...statusListState, ...action.status]
      } else {
        newState = action.status
      }
      break
    }
    case 'delete': {
      newState = statusListState.filter(function (statusListItem: statusType) {
        const contains = action.status.includes(statusListItem) ?? false
        return contains
      })
      break
    }
    default:
      newState = statusListState
  }

  return newState
}

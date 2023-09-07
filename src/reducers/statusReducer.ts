import { statusListType, statusReducerData, statusType } from 'breezeTypes'

export default function statusReducer (statusListState: statusListType, action: statusReducerData): statusListType {
  let currentStatusState: statusListType = []

  switch (action.type) {
    case 'create': {
      currentStatusState = currentStatusState.concat(action.status)
      break
    }
    case 'delete': {
      currentStatusState = currentStatusState.filter(function (currentStatusItem: statusType) {
        return statusListState.includes(currentStatusItem) === false
      })
      break
    }
    default:
      currentStatusState = statusListState
  }

  return currentStatusState
}

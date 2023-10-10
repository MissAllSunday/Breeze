import { statusDispatchContextType } from 'breezeTypes'
import { statusListType, statusReducerData, statusType } from 'breezeTypes'
import React, { createContext, useReducer } from 'react'

import StatusList from "../components/StatusList";

export const StatusContext: statusListType = createContext([])
export const StatusDispatchContext = createContext<statusDispatchContextType>(null)

export function statusReducer (statusListState: statusListType, action: statusReducerData): statusListType {
  let currentStatusState: statusListType = StatusContext

  switch (action.type) {
    case 'create': {
      currentStatusState = currentStatusState.concat(action.statusListState)
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

export function StatusProvider() {
  const [statusListState, dispatch] = useReducer(statusReducer, []);

  return (
    <StatusContext.Provider value={statusListState}>
      <StatusDispatchContext.Provider value={dispatch}>
        <StatusList />
      </StatusDispatchContext.Provider>
    </StatusContext.Provider>
  );
}
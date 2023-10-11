import {
  StatusDispatchContextType, StatusListType, StatusReducerData, StatusType,
} from 'breezeTypes';
import React, { createContext, useReducer } from 'react';

import StatusList from '../components/StatusList';

export const StatusContext: StatusListType = createContext([]);
export const StatusDispatchContext = createContext<StatusDispatchContextType>(null);

export function statusReducer(statusListState: StatusListType, action: StatusReducerData): StatusListType {
  let currentStatusState: StatusListType = StatusContext;

  switch (action.type) {
    case 'create': {
      currentStatusState = currentStatusState.concat(action.statusListState);
      break;
    }
    case 'delete': {
      currentStatusState = currentStatusState.filter((currentStatusItem: StatusType) => statusListState.includes(currentStatusItem) === false);
      break;
    }
    default:
      currentStatusState = statusListState;
  }

  return currentStatusState;
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

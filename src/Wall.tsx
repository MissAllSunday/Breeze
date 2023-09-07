import {
  getStatus, ServerGetStatusResponse
} from './api/StatusApi'
import React, { useEffect, useReducer, useState } from 'react'
import { statusListType, wallProps } from 'breezeTypes'
import Loading from './components/Loading'
import { Toaster } from 'react-hot-toast'
import statusReducer from './reducers/statusReducer'
import { StatusContext, StatusDispatchContext } from './context/statusContext'
import StatusList from './components/StatusList'
export default function Wall (props: wallProps): React.ReactElement {
  const [statusListState, dispatch] = useReducer(statusReducer, [])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    setIsLoading(true)
    getStatus(props.wallType)
      .then((statusListResponse: ServerGetStatusResponse) => {
        const fetchedStatusList: statusListType = Object.values(statusListResponse.content.data)
        dispatch({ type: 'create', status: fetchedStatusList })
      })
      .catch(exception => {
      })
      .finally(() => {
        setIsLoading(false)
      })
  }, [props.wallType])

  return (<div>
    <Toaster/>
    {isLoading
      ? <Loading/>
      : <><StatusContext.Provider value={statusListState}>
          <StatusDispatchContext.Provider value={dispatch}>
            <StatusList statusList={statusListState} />
          </StatusDispatchContext.Provider>
        </StatusContext.Provider>
      </>
      }
  </div>)
}

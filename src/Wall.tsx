import {
  getStatus, ServerGetStatusResponse
} from './api/StatusApi'
import React, { useEffect, useReducer, useState } from 'react'
import { wallProps } from 'breezeTypes'
import Loading from './components/Loading'
import StatusList from './components/StatusList'
import { Toaster } from 'react-hot-toast'
import statusReducer from './reducers/status'

export default function Wall (props: wallProps): React.ReactElement {
  const [statusListState, dispatch] = useReducer(statusReducer, [])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    setIsLoading(true)
    getStatus(props.wallType)
      .then((response) => response.json())
      .then((statusListResponse: ServerGetStatusResponse) => {
        dispatch({ type: 'create', status: statusListResponse.content.data })
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
      : <StatusList
        statusList={statusListState} />}
  </div>)
}

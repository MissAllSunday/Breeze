import { statusType, StatusListProps } from 'breezeTypes'
import Status from './Status'
import React, { useCallback, useEffect, useReducer, useState } from 'react'
import { deleteStatus, postStatus, ServerPostStatusResponse } from '../api/StatusApi'
import Loading from './Loading'
import Editor from './Editor'
import smfTextVars from '../DataSource/Txt'
import toast from 'react-hot-toast'
import statusReducer from '../reducers/status'

function StatusstatusListState (props: StatusListProps): React.ReactElement {
  const [statusListState, dispatch] = useReducer(statusReducer, props.statusList)
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    if (statusListState.length === 0) {
      toast.error(smfTextVars.error.noStatus)
    }
  }, [statusListState])

  const createStatus = useCallback((content: string) => {
    setIsLoading(true)
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        const statusKeys = Object.keys(response.content)
        statusKeys.map((value, index) => {
          return dispatch({ type: 'create', status: response.content[value] })
        })
        toast.success(response.message)
      }).catch(exception => {
        toast.error(exception.toString())
      }).finally(() => {
        setIsLoading(false)
      })
  }, [])

  const removeStatus = useCallback((status: statusType) => {
    setIsLoading(true)
    deleteStatus(status.id).then((response) => {
      dispatch({ type: 'delete', status })
      toast.success(response.message)
    }).catch(exception => {
      toast.error(exception.toString())
    })
      .finally(() => {
        setIsLoading(false)
      })
  }, [])

  return (
    <div>
      {isLoading
        ? <Loading />
        : <Editor saveContent={createStatus} />}
      <ul className="status">
        {statusListState.map((status: statusType) => (
          <Status
            key={status.id}
            status={status}
            removeStatus={removeStatus}
          />
        ))}
          </ul>
    </div>
  )
}

export default React.memo(StatusstatusListState)

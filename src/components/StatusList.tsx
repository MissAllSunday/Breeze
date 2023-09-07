import { StatusListProps, statusListType, statusType } from 'breezeTypes'

import React, { useCallback, useContext, useState } from 'react'
import { deleteStatus, postStatus, ServerPostStatusResponse } from '../api/StatusApi'
import Loading from './Loading'
import Editor from './Editor'
import smfTextVars from '../DataSource/Txt'
import toast from 'react-hot-toast'
import { StatusContext, StatusDispatchContext } from '../context/statusContext'
import Status from './Status'

function StatusstatusListState (props: StatusListProps): React.ReactElement {
  const [isLoading, setIsLoading] = useState(false)
  const statusListState: statusListType = useContext(StatusContext)
  const statusDispatch = useContext(StatusDispatchContext)

  if (statusListState.length === 0) {
    toast.error(smfTextVars.error.noStatus)
  }

  const createStatus = useCallback((content: string) => {
    setIsLoading(true)
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        statusDispatch({ type: 'create', statusListState: Object.values(response.content) })
        toast.success(response.message)
      }).catch(exception => {
        toast.error(exception.toString())
      }).finally(() => {
        setIsLoading(false)
      })
  }, [statusDispatch])

  const removeStatus = useCallback((status: statusType) => {
    setIsLoading(true)
    deleteStatus(status.id).then((response) => {
      statusDispatch({ type: 'delete', statusListState: [status] })
      toast.success(response.message)
    }).catch(exception => {
      toast.error(exception.toString())
    })
      .finally(() => {
        setIsLoading(false)
      })
  }, [statusDispatch])

  console.log(statusListState)

  return (
    <div>
      {isLoading
        ? <Loading />
        : <><Editor saveContent={createStatus}/>
           <ul className="status">
            {statusListState.forEach((status: statusType) => (
              <Status
                key={status.id}
                status={status}
                removeStatus={removeStatus}
              />
            ))}
           </ul>
        </> }
    </div>
  )
}

export default React.memo(StatusstatusListState)

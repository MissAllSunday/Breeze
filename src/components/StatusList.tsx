import { statusType, StatusListProps, statusListType } from 'breezeTypes'
import Status from './Status'
import React, { useCallback, useEffect, useState } from 'react'
import { deleteStatus, postStatus, ServerPostStatusResponse } from '../api/StatusApi'
import Loading from './Loading'
import Editor from './Editor'
import smfTextVars from '../DataSource/Txt'
import toast from 'react-hot-toast'

function StatusList (props: StatusListProps): React.ReactElement {
  const [list, setList] = useState<statusListType>(props.statusList)
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    if (list.length === 0) {
      toast.error(smfTextVars.error.noStatus)
    }
  }, [list])

  const createStatus = useCallback((content: string) => {
    setIsLoading(true)
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        toast.success(response.message)
        setList((prevList: statusListType) => {
          return [...prevList, ...Object.values(response.content)]
        })
      }).catch(exception => {
        toast.error(exception.toString())
      }).finally(() => {
        setIsLoading(false)
      })
  }, [])

  const removeStatus = useCallback((status: statusType) => {
    setIsLoading(true)
    deleteStatus(status.id).then((response) => {
      toast.success(response.message)
      setList((prevList: statusType[]) => prevList.filter((statusListItem: statusType) => {
        return statusListItem.id !== status.id
      }))
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
        {list.map((status: statusType) => (
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

export default React.memo(StatusList)

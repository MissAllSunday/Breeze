import { statusType, StatusListProps, statusListType } from 'breezeTypes'
import Status from './Status'
import React, { useCallback, useState } from 'react'
import { deleteStatus, postStatus, ServerPostStatusResponse } from '../api/StatusApi'
import Loading from './Loading'
import Editor from './Editor'

function StatusList (props: StatusListProps): React.ReactElement {
  const [list, setList] = useState<statusListType>(props.statusList)
  const [isLoading, setIsLoading] = useState(false)

  const createStatus = useCallback((content: string) => {
    setIsLoading(true)
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        console.log(response)
        setList((prevList: statusListType) => {
          return [...prevList, ...Object.values(response.content)]
        })
      }).catch(exception => {
      }).finally(() => {
        setIsLoading(false)
      })
  }, [])

  const removeStatus = useCallback((status: statusType) => {
    setIsLoading(true)
    deleteStatus(status.id).then((response) => {
      if (response.status !== 204) {
        // Show some error message
        return
      }

      setList((prevList: statusType[]) => prevList.filter((statusListItem: statusType) => {
        return statusListItem.id !== status.id
      }))
    }).catch(exception => {
    })
      .finally(() => {
        setIsLoading(false)
      })
  }, [])

  return (
    <div>
      {isLoading
        ? <Loading />
        : <Editor saveContent={createStatus} />
      }
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

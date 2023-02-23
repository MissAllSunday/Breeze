import {
  getStatus
} from './api/StatusApi'
import React, { useEffect, useState } from 'react'
import { statusType, statusListType, wallProps } from 'breezeTypes'
import Loading from './components/Loading'
import StatusList from './components/StatusList'

export default function Wall (props: wallProps): React.ReactElement {
  const [list, setList] = useState<statusListType>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    console.log(props.wallType)
    getStatus(props.wallType)
      .then((response) => response.json())
      .then((statusList: statusListType) => {
        const newStatus: statusListType = Object.values(statusList.content)
        setList(newStatus.map((status: statusType) => {
          status.comments = Object.values(status.comments)

          return status
        }))
      })
      .catch(exception => {
      })
      .finally(() => {
        setIsLoading(false)
      })
  }, [props.wallType])

  return (<div>
    {isLoading
      ? <Loading />
      : <StatusList
          statusList={list} />
    }
  </div>)
}

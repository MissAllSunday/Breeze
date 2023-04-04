import {
  getStatus, ServerGetStatusResponse
} from './api/StatusApi'
import React, { useEffect, useState } from 'react'
import { statusType, statusListType, wallProps } from 'breezeTypes'
import Loading from './components/Loading'
import StatusList from './components/StatusList'
import { Toaster } from 'react-hot-toast'

export default function Wall (props: wallProps): React.ReactElement {
  const [list, setList] = useState<statusListType>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    setIsLoading(true)
    getStatus(props.wallType)
      .then((response) => response.json())
      .then((statusListResponse: ServerGetStatusResponse) => {
        const newStatus: statusListType = Object.values(statusListResponse.content.data)

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
    <Toaster/>
    {isLoading
      ? <Loading/>
      : <StatusList
        statusList={list} />}
  </div>)
}

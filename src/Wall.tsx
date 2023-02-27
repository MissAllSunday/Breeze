import {
  getStatus
} from './api/StatusApi'
import React, { useEffect, useState } from 'react'
import { statusType, statusListType, wallProps, noticeProps } from 'breezeTypes'
import Loading from './components/Loading'
import StatusList from './components/StatusList'
import Notice from './components/Notice'
import smfTextVars from './DataSource/Txt'

export default function Wall (props: wallProps): React.ReactElement {
  const [list, setList] = useState<statusListType>([])
  const [isLoading, setIsLoading] = useState(true)
  const [notice, setNotice] = useState<noticeProps>({
    show: false,
    options: {
      type: 'noticebox',
      header: '',
      body: smfTextVars.general.noStatus
    }
  })

  useEffect(() => {
    setIsLoading(true)
    getStatus(props.wallType)
      .then((response) => response.json())
      .then((statusList: statusListType) => {
        const newStatus: statusListType = Object.values(statusList.content)

        if (newStatus.length === 0) {
          setNotice((prevNotice: noticeProps) => {
            return { ...prevNotice, ...{ show: true } }
          })
          return
        }

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
    {<Notice
      options={notice.options}
      show={notice.show}/>}
    {isLoading
      ? <Loading/>
      : <StatusList
        statusList={list} />}
  </div>)
}

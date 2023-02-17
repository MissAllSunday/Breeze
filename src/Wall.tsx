import {
  ServerStatusResponse,
  getStatus,
  deleteStatus,
  postStatus,
  ServerPostStatusResponse
} from './api/StatusApi'
import React, { useCallback, useEffect, useState } from 'react'
import { statusType, statusListType, commentType, commentList, wallProps } from 'breezeTypes'
import Loading from './components/Loading'
import Editor from './components/Editor'
import { AxiosResponse } from 'axios'
import { deleteComment } from './api/CommentApi'
import StatusList from './components/StatusList'

export default function Wall (props: wallProps): React.ReactElement {
  const [list, setList] = useState<statusListType>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    setIsLoading(true)
    getStatus(props.wallType)
      .then((response: ServerStatusResponse) => {
        const newStatus: statusListType = Object.values(response.data.content)
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

  const removeStatus = useCallback((status: statusType) => {
    setIsLoading(true)
    deleteStatus(status.id).then((response) => {
      if (response.status !== 204) {
        // Show some error message
        return
      }

      setList((prevList: statusType[]) => prevList.map((statusListItem: statusType) => {
        return statusListItem.id !== status.id
      }))
    }).catch(exception => {
    })
      .finally(() => {
        setIsLoading(false)
      })
  }, [])

  const removeComment = useCallback((status: statusType, comment: commentType) => {
    setIsLoading(true)

    deleteComment(comment.id).then((response) => {
      if (response.status !== 204) {
        return
      }

      setList((prevList: statusType[]) => prevList.map((statusListItem: statusType) => {
        statusListItem.comments = statusListItem.comments.filter(function (commentListItem: commentType) {
          return commentListItem.id !== comment.id
        })
        return statusListItem
      }))
    }).catch(exception => {
    })
      .finally(() => {
        setIsLoading(false)
      })
  }, [])

  const onCreateComment = useCallback((commentList: commentList, statusID: number) => {
    setIsLoading(true)
    setList((prevList: statusType[]) => prevList.map((statusListItem: statusType) => {
      if (statusListItem.id === statusID) {
        statusListItem.comments = [...statusListItem.comments, commentList.pop()]
      }

      return statusListItem
    }))
    setIsLoading(false)
  }, [])

  const onCreateStatus = useCallback((content: string) => {
    setIsLoading(true)
    postStatus(content).then((response: AxiosResponse<ServerPostStatusResponse>) => {
      if (response.status !== 201) {
        return
      }

      setList((prevState: statusListType) => {
        return [...prevState, ...Object.values(response.data.content)]
      })
    }).catch(exception => {
    }).finally(() => {
      setIsLoading(false)
    })
  }, [])

  return (<div>
    {isLoading
      ? <Loading />
      : <>
        <div>
          {isLoading
            ? <Loading />
            : <Editor saveContent={onCreateStatus} />
          }
        </div>
        <StatusList
          statusList={list}
          removeStatus={removeStatus}
          removeComment={removeComment}
          onCreateComment={onCreateComment}/>
      </>}
  </div>)
}

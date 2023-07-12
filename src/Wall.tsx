import {
  deleteStatus,
  getStatus, postStatus, ServerGetStatusResponse, ServerPostStatusResponse
} from './api/StatusApi'
import React, { useCallback, useEffect, useReducer, useRef, useState } from 'react'
import { statusType, wallProps } from 'breezeTypes'
import Loading from './components/Loading'
import StatusList from './components/StatusList'
import toast, { Toaster } from 'react-hot-toast'
import statusReducer from './reducers/status'
import smfTextVars from './DataSource/Txt'

export default function Wall (props: wallProps): React.ReactElement {
  const [statusListState, dispatch] = useReducer(statusReducer, {})
  const [isLoading, setIsLoading] = useState(true)
  const observerTarget = useRef(null)

  if (Object.keys(statusListState).length === 0) {
    toast.error(smfTextVars.error.noStatus)
  }

  useEffect(() => {
    const currentObserver = observerTarget
    const observer = new IntersectionObserver(
      entries => {
        if (entries[0].isIntersecting) {
          setIsLoading(true)
          getStatus(props.wallType)
            .then(async (response) => await response.json())
            .then((statusListResponse: ServerGetStatusResponse) => {
              dispatch({ type: 'create', status: statusListResponse.content.data })
            })
            .catch(exception => {
            })
            .finally(() => {
              setIsLoading(false)
            })
        }
      },
      { threshold: 1 }
    )

    if (currentObserver.current !== null) {
      observer.observe(currentObserver.current)
    }

    return () => {
      if (currentObserver.current !== null) {
        observer.unobserve(currentObserver.current)
      }
    }
  }, [props.wallType, observerTarget])

  const onStatusDelete = useCallback((statusToDelete: statusType) => {
    setIsLoading(true)
    deleteStatus(statusToDelete.id).then((response) => {
      dispatch({ type: 'delete', status: { statusToDelete } })
      toast.success(response.message)
    }).catch(exception => {
      toast.error(exception.toString())
    })
      .finally(() => {
        setIsLoading(false)
      })
  }, [])

  const onStatusCreate = useCallback((content: string) => {
    setIsLoading(true)
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        dispatch({ type: 'create', status: response.content })
        toast.success(response.message)
      }).catch(exception => {
        toast.error(exception.toString())
      }).finally(() => {
        setIsLoading(false)
      })
  }, [])

  return (<div>
    <Toaster/>
    {isLoading &&
      <Loading/> }
    <StatusList statusList={undefined} onDelete={onStatusDelete} onCreate={onStatusCreate } />
    <div ref={observerTarget}></div>
  </div>)
}

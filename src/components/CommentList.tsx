import { CommentListProps,commentType } from 'breezeTypes'
import React, { useCallback, useReducer, useState } from 'react'
import toast from 'react-hot-toast'

import { deleteComment, postComment } from '../api/CommentApi'
import commentsReducer from '../reducers/commentsReducer'
import Comment from './Comment'
import Editor from './Editor'
import Loading from './Loading'

function CommentList (props: CommentListProps): React.ReactElement {
  const [isLoading, setIsLoading] = useState(false)
  const [commentListState, dispatch] = useReducer(commentsReducer, props.commentList)

  const createComment = useCallback((content: string) => {
    setIsLoading(true)

    postComment({
      statusID: props.statusId,
      body: content
    }).then((response) => {
      const commentKeys = Object.keys(response.content)
      commentKeys.map((value, index) => {
        return dispatch({ type: 'create', comment: response.content[value] })
      })

      toast.success(response.message)
    }).catch(exception => {
      toast.error(exception.toString())
    }).finally(() => {
      setIsLoading(false)
    })
  }, [props.statusId])

  const removeComment = useCallback((comment: commentType) => {
    setIsLoading(true)
    deleteComment(comment.id).then((response) => {
      toast.success(response.message)

      dispatch({ type: 'delete', comment })
    }).catch(exception => {
      toast.error(exception.toString())
    }).finally(() => {
      setIsLoading(false)
    })
  }, [])

  return (
    <div>
    <div className='comment_posting'>
      {isLoading
        ? <Loading />
        : <Editor saveContent={createComment} />}
    </div>
  <ul className="status">
    {commentListState.map((comment: commentType) => (
      <Comment
        key={comment.id}
        comment={comment}
        removeComment={removeComment}
      />
    ))}
  </ul>
    </div>
  )
}

export default React.memo(CommentList)

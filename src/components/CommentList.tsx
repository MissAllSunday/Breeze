import React, { useCallback, useState } from 'react'
import Comment from './Comment'
import { commentType, CommentListProps } from 'breezeTypes'
import Loading from './Loading'
import Editor from './Editor'
import { deleteComment, postComment } from '../api/CommentApi'

function CommentList (props: CommentListProps): React.ReactElement {
  const [isLoading, setIsLoading] = useState(false)
  const [list, setList] = useState(props.commentList)

  const createComment = useCallback((content: string) => {
    setIsLoading(true)

    postComment({
      statusID: props.statusId,
      body: content
    }).then((response) => {
      setList((prevList: commentType[]) => {
        return [...prevList, ...Object.values(response.content)]
      })
    }).catch(() => {}).finally(() => {
      setIsLoading(false)
    })
  }, [props.statusId])

  const removeComment = useCallback((comment: commentType) => {
    setIsLoading(true)
    deleteComment(comment.id).then((response) => {
      if (response.status !== 204) {
        return
      }

      setList((prevList: commentType[]) => prevList.filter(function (commentListItem: commentType) {
        return commentListItem.id !== comment.id
      }))
    }).catch(exception => {
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
    {list.map((comment: commentType) => (
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

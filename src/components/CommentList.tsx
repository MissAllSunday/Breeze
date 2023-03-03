import React, { useCallback, useState } from 'react'
import Comment from './Comment'
import { commentType, CommentListProps } from 'breezeTypes'
import Loading from './Loading'
import Editor from './Editor'
import { deleteComment, postComment } from '../api/CommentApi'
import toast from 'react-hot-toast'

function CommentList (props: CommentListProps): React.ReactElement {
  const [isLoading, setIsLoading] = useState(false)
  const [list, setList] = useState(props.commentList)

  const createComment = useCallback((content: string) => {
    setIsLoading(true)

    postComment({
      statusID: props.statusId,
      body: content
    }).then((response) => {
      toast.success(response.message)
      setList((prevList: commentType[]) => {
        return [...prevList, ...Object.values(response.content)]
      })
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

      setList((prevList: commentType[]) => prevList.filter(function (commentListItem: commentType) {
        return commentListItem.id !== comment.id
      }))
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

import React from 'react'
import Comment from './Comment'
import { commentType, CommentListProps } from 'breezeTypes'

export const CommentList = ({ commentList, removeComment }: CommentListProps): React.ReactElement<Comment> => (
  <ul className="status">
    {commentList.map((comment: commentType) => (
      <Comment
        key={comment.id}
        comment={comment}
        removeComment={removeComment}
      />
    ))}
  </ul>
)

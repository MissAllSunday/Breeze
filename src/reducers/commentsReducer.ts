import { commentList, commentReducerData, commentType } from 'breezeTypes'

export default function commentsReducer (commentListState: commentList, action: commentReducerData): commentList {
  let newState: commentList

  switch (action.type) {
    case 'create': {
      newState = [...commentListState, action.comment]
      break
    }
    case 'delete': {
      newState = commentListState.filter(function (commentListItem: commentType) {
        return commentListItem.id !== action.comment.id
      })
      break
    }
    default:
      newState = commentListState
  }

  return newState
}

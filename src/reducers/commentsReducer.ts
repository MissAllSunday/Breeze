import { CommentList, CommentReducerData, CommentType } from 'breezeTypes';

export default function commentsReducer(CommentListState: CommentList, action: CommentReducerData): CommentList {
  let newState: CommentList;

  switch (action.type) {
    case 'create': {
      newState = [...CommentListState, action.comment];
      break;
    }
    case 'delete': {
      newState = CommentListState.filter((CommentListItem: CommentType) => CommentListItem.id !== action.comment.id);
      break;
    }
    default:
      newState = CommentListState;
  }

  return newState;
}

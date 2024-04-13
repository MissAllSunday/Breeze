import { CommentListType } from 'breezeTypes';

import smfVars from '../DataSource/SMF';
import { baseConfig, baseUrl } from './Api';

interface ServerCommentData {
  message: string
  content: CommentListType
}

interface ServerDeleteComment {
  message: string
  content: object
}

const action = 'breezeComment';

export const postComment = async (commentParams: object): Promise<ServerCommentData> => {
  const postCommentResults = await fetch(baseUrl(action, 'postComment'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      ...commentParams,
      userId: smfVars.userId,
    })),
  });

  return postCommentResults.ok
    ? postCommentResults.json()
    : postCommentResults.json().then((errorResponse) => { throw Error(errorResponse.message); });
};

export const deleteComment = async (commentId: number): Promise<ServerDeleteComment> => {
  const deleteCommentResults = await fetch(baseUrl(action, 'deleteComment'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: commentId,
      userId: smfVars.userId,
    })),
  });

  return deleteCommentResults.ok
    ? deleteCommentResults.json()
    : deleteCommentResults.json().then((errorResponse) => { throw Error(errorResponse); });
};

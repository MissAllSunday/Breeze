import { CommentListType } from 'breezeTypesComments';

import smfVars from '../DataSource/SMF';
import { showError } from '../utils/tooltip';
import { baseConfig, baseUrl } from './Api';

export interface ServerCommentData {
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

  return postCommentResults.ok ? postCommentResults.json() : showError(postCommentResults);
};

export const deleteComment = async (commentId: number): Promise<ServerDeleteComment> => {
  const deleteCommentResults = await fetch(baseUrl(action, 'deleteComment'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: commentId,
      userId: smfVars.userId,
    })),
  });

  return deleteCommentResults.ok ? deleteCommentResults.json() : showError(deleteCommentResults);
};

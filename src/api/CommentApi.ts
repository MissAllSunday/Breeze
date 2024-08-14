import { CommentListType } from 'breezeTypesComments';

import { IServerActions } from '../customTypings/actions';
import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showError, showErrorMessage, showInfo } from '../utils/tooltip';
import { baseConfig, baseUrl, safeDelete } from './Api';

export interface ServerCommentData {
  message: string
  content: CommentListType
}

interface ServerDeleteComment {
  message: string
  content: object
}

const action:IServerActions = 'breezeComment';

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

export const deleteComment = async (commentId: number): Promise<boolean> => {
  const deleteCommentResults = await fetch(baseUrl(action, 'deleteComment'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: commentId,
      userId: smfVars.userId,
    })),
  });

  return safeDelete(deleteCommentResults, smfTextVars.general.deletedComment);
};

import { CommentListType } from 'breezeTypesComments';

import { IServerActions } from '../customTypings/actions';
import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showErrorMessage } from '../utils/tooltip';
import { baseConfig, baseUrl, safeDelete, safePost } from './Api';

const action:IServerActions = 'breezeComment';

export const postComment = async (commentParams: object): Promise<CommentListType> => {
  try {
    const postCommentResults = await fetch(baseUrl(action, 'postComment'), {
      method: 'POST',
      body: JSON.stringify(baseConfig({
        ...commentParams,
        userId: smfVars.userId,
      })),
    });

    return await safePost(postCommentResults);
  } catch (error:unknown) {
    showErrorMessage(smfTextVars.error.generic);
  }
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

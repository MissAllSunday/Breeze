import { CommentListType } from 'breezeTypesComments';

import smfVars from '../../DataSource/SMF';
import smfTextVars from '../../DataSource/Txt';
import { showError } from '../../utils/tooltip';
import { baseConfig, baseUrl, resolvePost } from '../Api';

export const postComment = async (commentParams: object): Promise<CommentListType> => {
  try {
    const postCommentResults = await fetch(baseUrl('breezeComment', 'postComment'), {
      method: 'POST',
      body: JSON.stringify(baseConfig({
        ...commentParams,
        userId: smfVars.userId,
      })),
    });

    return await resolvePost(postCommentResults);
  } catch (error:unknown) {
    showError(smfTextVars.error.generic);
  }
};

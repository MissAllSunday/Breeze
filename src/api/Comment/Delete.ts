import smfVars from '../../DataSource/SMF';
import smfTextVars from '../../DataSource/Txt';
import { baseConfig  } from '../Base';
import { baseUrl } from '../Base';
import { resolveDelete } from '../Resolvers/Delete';

export const deleteComment = async (commentId: number): Promise<boolean> => {
  const deleteCommentResults = await fetch(baseUrl('breezeComment', 'deleteComment'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: commentId,
      userId: smfVars.userId,
    })),
  });

  return resolveDelete(deleteCommentResults, smfTextVars.general.deletedComment);
};

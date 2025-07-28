import type { CommentListType } from 'breezeTypesComments';
import type { LikeType } from 'breezeTypesLikes';
import type { StatusListType } from 'breezeTypesStatus';

import { showInfo } from '../../utils/tooltip';

export const resolvePost = async (response: Response):Promise<StatusListType | CommentListType | LikeType | undefined> => {
  const { content, message } = await response.json();

  if (response.ok && response.status === 201) {
    showInfo(message);

    return content;
  }
};

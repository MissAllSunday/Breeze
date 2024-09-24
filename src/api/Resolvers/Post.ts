import { CommentListType } from 'breezeTypesComments';
import { LikeInfoState, LikeType } from 'breezeTypesLikes';
import { StatusListType } from 'breezeTypesStatus';

import { showInfo } from '../../utils/tooltip';

export const resolvePost = async (response: Response):Promise<StatusListType | CommentListType | LikeType | void> => {
  const { content, message } = await response.json();

  if (response.ok && response.status === 201) {
    showInfo(message);

    return content;
  }
};

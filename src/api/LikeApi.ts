import { LikeInfoState, LikeType } from 'breezeTypesLikes';

import { IServerActions } from '../customTypings/actions';
import SmfVars from '../DataSource/SMF';
import { showError } from '../utils/tooltip';
import { baseConfig, baseUrl } from './Api';

export interface ServerLikeData {
  content: LikeType
  message: string
}

export interface ServerLikeInfoData {
  message: string
  content: LikeInfoState[]
}

const action:IServerActions = 'breezeLike';

export const postLike = async (likeData: LikeType): Promise<ServerLikeData> => {
  const params = {
    id_member: SmfVars.userId,
    content_type: likeData.type,
    content_id: likeData.contentId,
  };

  const likeResults = await fetch(baseUrl(action, 'like'), {
    method: 'POST',
    body: JSON.stringify(baseConfig(params)),
  });

  return likeResults.json();
};

export const getLikeInfo = async (like: LikeType): Promise<ServerLikeInfoData> => {
  const params = {
    content_type: like.type,
    content_id: like.contentId,
  };

  const likeInfoResults = await fetch(baseUrl(action, 'info'), {
    method: 'POST',
    body: JSON.stringify(baseConfig(params)),
  });

  return likeInfoResults.ok ? likeInfoResults.json() : showError(likeInfoResults);
};

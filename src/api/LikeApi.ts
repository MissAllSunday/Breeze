import { LikeInfoState, LikeType } from 'breezeTypesLikes';

import { IServerActions } from '../customTypings/actions';
import SmfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showError } from '../utils/tooltip';
import { baseConfig, baseUrl, safeFetch, safePost } from './Api';

export interface ServerLikeData {
  content: LikeType
  message: string
}

export interface ServerLikeInfoData {
  message: string
  content: LikeInfoState[]
}

export interface IPostLikeParams {
  id_member: number;
  content_type: string;
  content_id: number;
}

const action:IServerActions = 'breezeLike';

export const postLike = async (likeData: LikeType): Promise<any> => {
  try {
    const params:IPostLikeParams = {
      id_member: SmfVars.userId,
      content_type: likeData.type,
      content_id: likeData.contentId,
    };

    const likeResults = await fetch(baseUrl(action, 'like'), {
      method: 'POST',
      body: JSON.stringify(baseConfig(params)),
    });

    return await safePost(likeResults);
  } catch (error:unknown) {
    showError(smfTextVars.error.generic);
  }
};

export const getLikeInfo = async (like: LikeType):Promise<any> => {
  try {
    const params = {
      content_type: like.type,
      content_id: like.contentId,
    };
    const response =  await fetch(baseUrl(action, 'info', [ params ]), {
      method: 'GET',
      headers: {
        'X-SMF-AJAX': '1',
      },
    });

    return await safeFetch(response);
  } catch (error:unknown) {
    showError(smfTextVars.error.generic);
  }
};

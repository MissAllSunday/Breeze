import { LikeType } from 'breezeTypesLikes';

import SmfVars from '../../DataSource/SMF';
import smfTextVars from '../../DataSource/Txt';
import { showError } from '../../utils/tooltip';
import {baseConfig} from "../BaseConfig";
import {baseUrl} from "../BaseUrl";
import {resolvePost} from "../Resolvers/Post";

export interface IPostLikeParams {
  id_member: number;
  content_type: string;
  content_id: number;
}

export const postLike = async (likeData: LikeType): Promise<any> => {
  try {
    const params:IPostLikeParams = {
      id_member: SmfVars.userId,
      content_type: likeData.type,
      content_id: likeData.contentId,
    };

    const likeResults = await fetch(baseUrl('breezeLike', 'like'), {
      method: 'POST',
      body: JSON.stringify(baseConfig(params)),
    });

    return await resolvePost(likeResults);
  } catch (error:unknown) {
    showError(smfTextVars.error.generic);
  }
};

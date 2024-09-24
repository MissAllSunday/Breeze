import { LikeInfoState, LikeType } from 'breezeTypesLikes';

import smfTextVars from '../../DataSource/Txt';
import { showError } from '../../utils/tooltip';
import { baseUrl, resolveGet } from '../BaseConfig';

export const getLikeInfo = async (like: LikeType):Promise<any> => {
  try {
    const params = {
      content_type: like.type,
      content_id: like.contentId,
    };
    const response =  await fetch(baseUrl('breezeLike', 'info',
      [ params ]), {
      method: 'GET',
      headers: {
        'X-SMF-AJAX': '1',
      },
    });

    return await resolveGet(response);
  } catch (error:unknown) {
    showError(smfTextVars.error.generic);
  }
};

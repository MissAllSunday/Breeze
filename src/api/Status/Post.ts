import { StatusListType } from 'breezeTypesStatus';

import smfVars from '../../DataSource/SMF';
import smfTextVars from '../../DataSource/Txt';
import { showError } from '../../utils/tooltip';
import { baseConfig } from '../Base';
import { baseUrl } from '../Base';
import { resolvePost } from '../Resolvers/Post';

export const postStatus = async (content: string): Promise<StatusListType> => {
  try {
    const response = await fetch(baseUrl('breezeStatus', 'postStatus'), {
      method: 'POST',
      body: JSON.stringify(baseConfig({
        wallId: smfVars.wallId,
        userId: smfVars.userId,
        body: content,
      })),
    });

    return await resolvePost(response);
  } catch (error:unknown) {
    showError(smfTextVars.error.generic);
  }
};

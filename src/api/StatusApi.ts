import { StatusListType } from 'breezeTypesStatus';

import { IServerActions } from '../customTypings/actions';
import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showErrorMessage } from '../utils/tooltip';
import { baseConfig, baseUrl, IFetchStatus, safeDelete, safeFetch, safePost } from './Api';

const action:IServerActions = 'breezeStatus';

export const getStatus = async (type: string, start: number): Promise<any | void> => {
  try {
    const response =  await fetch(baseUrl(action, type, [ { start: start, wallId: smfVars.wallId } ]), {
      method: 'GET',
      headers: {
        'X-SMF-AJAX': '1',
      },
    });

    return await safeFetch(response);
  } catch (error:unknown) {
    showErrorMessage(smfTextVars.error.generic);
  }
};

export const deleteStatus = async (statusId: number): Promise<boolean> => {
  const deleteStatusResults = await fetch(baseUrl(action, 'deleteStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: statusId,
      userId: smfVars.userId,
    })),
  });

  return safeDelete(deleteStatusResults, smfTextVars.general.deletedStatus);
};

export const postStatus = async (content: string): Promise<StatusListType> => {
  try {
    const response = await fetch(baseUrl(action, 'postStatus'), {
      method: 'POST',
      body: JSON.stringify(baseConfig({
        wallId: smfVars.wallId,
        userId: smfVars.userId,
        body: content,
      })),
    });

    return await safePost(response);
  } catch (error:unknown) {
    showErrorMessage(smfTextVars.error.generic);
  }
};

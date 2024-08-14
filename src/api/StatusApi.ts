import { PermissionsContextType } from 'breezeTypesPermissions';
import { StatusListType } from 'breezeTypesStatus';

import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showError, showErrorMessage, showInfo } from '../utils/tooltip';
import { baseConfig, baseUrl, safeFetch } from './Api';

export interface ServerDeleteStatusResponse {
  content: object
  message: string
}

export interface ServerPostStatusResponse {
  content: StatusListType
  message: string
  type: string
}

export interface ServerGetStatusResponse {
  content: { total: number, data: StatusListType, permissions: PermissionsContextType }
  message: string
}

const action = 'breezeStatus';

export const getStatus = async (type: string, start: number): Promise<StatusListType> => {
  try {
    const response =  await fetch(baseUrl(action, type, [ { start: start } ]), {
      method: 'POST',
      body: JSON.stringify(baseConfig({
        wallId: smfVars.wallId,
      })),
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

  const deleted = deleteStatusResults.ok && deleteStatusResults.status === 204;

  if (!deleted) {
    const { message } = await deleteStatusResults.json();
    showErrorMessage(message);
  } else {
    showInfo(smfTextVars.general.deletedStatus);
  }

  return deleted;
};

export const postStatus = async (content: string): Promise<ServerPostStatusResponse> => {
  const postStatusResults = await fetch(baseUrl(action, 'postStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      wallId: smfVars.wallId,
      userId: smfVars.userId,
      body: content,
    })),
  });

  return postStatusResults.ok ? postStatusResults.json() : showError(postStatusResults);
};

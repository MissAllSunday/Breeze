import { PermissionsContextType } from 'breezeTypesPermissions';
import { StatusListType } from 'breezeTypesStatus';

import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showError, showErrorMessage } from '../utils/tooltip';
import { baseConfig, baseUrl } from './Api';

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

    const { content, message } = await response.json();

    if (response.ok && response.status === 200) {
      return content;
    } else {
      showErrorMessage(message);
    }

  } catch (error:unknown) {
    showErrorMessage(smfTextVars.error.generic);
  }
};

export const deleteStatus = async (statusId: number): Promise<ServerDeleteStatusResponse> => {
  const deleteStatusResults = await fetch(baseUrl(action, 'deleteStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: statusId,
      userId: smfVars.userId,
    })),
  });

  return deleteStatusResults.ok ? deleteStatusResults.json() : showError(deleteStatusResults);
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

import { StatusListType } from 'breezeTypes';
import { useCallback } from 'react';

import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
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
  content: { total: number, data: StatusListType }
  message: string
}

const action = 'breezeStatus';

export const getStatus = async (type: string): Promise<StatusListType> => {
  const response = await fetch(baseUrl(action, type), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      wallId: smfVars.wallId,
    })),
  });

  if (response.ok) {
    return response.json();
  }

  throw new Error(smfTextVars.error.noStatus);
};

export const deleteStatus = async (statusId: number): Promise<ServerDeleteStatusResponse> => {
  const deleteStatusResults = await fetch(baseUrl(action, 'deleteStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: statusId,
      userId: smfVars.userId,
    })),
  });

  return await deleteStatusResults.ok
    ? deleteStatusResults.json()
    : deleteStatusResults.json().then((errorResponse) => { throw Error(errorResponse); });
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

  return await postStatusResults.ok
    ? postStatusResults.json()
    : postStatusResults.json().then((errorResponse) => { throw Error(errorResponse.message); });
};

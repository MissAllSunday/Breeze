import { StatusListType } from 'breezeTypes';

import smfVars from '../DataSource/SMF';
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
  const statusResults = await fetch(baseUrl(action, type), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      wallId: smfVars.wallId,
    })),
  });

  return statusResults.json();
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

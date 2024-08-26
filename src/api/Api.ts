import { PermissionsContextType } from 'breezeTypesPermissions';
import { StatusListType } from 'breezeTypesStatus';

import SmfVars from '../DataSource/SMF';
import { showErrorMessage, showInfo } from '../utils/tooltip';

export const baseUrl = (action: string, subAction: string, additionalParams: object[] = []): string => {
  const url = new URL(SmfVars.scriptUrl);

  url.searchParams.append('action', action);
  url.searchParams.append('sa', subAction);
  url.searchParams.append(SmfVars.session.var, SmfVars.session.id);

  additionalParams.map((objectValue): null => {
    for (const [key, value] of Object.entries(objectValue)) {
      url.searchParams.append(key, value);
    }

    return null;
  });

  return url.href;
};

export const baseConfig = (params: object = {}): object => ({
  data: params,
  headers: {
    'X-SMF-AJAX': '1',
  },
});

export interface IServerFetchResponse {
  data: StatusListType,
  permissions: PermissionsContextType,
  total: number
}
export interface IPostStatusResponse {
  content: StatusListType
  message: string
  type: string
}

export const safeFetch = async (response: Response):Promise<IServerFetchResponse | IPostStatusResponse | void> => {
  const { content, message } = await response.json();

  if (response.ok && response.status === 200) {
    return content;
  }

  if (message.length) {
    showErrorMessage(message);
  }
};

export const safeDelete = async (response: Response, successMessage: string):Promise<boolean> => {

  const deleted: boolean = response.ok && response.status === 204;

  if (!deleted) {
    const { message } = await response.json();
    showErrorMessage(message);
  } else {
    showInfo(successMessage);
  }

  return deleted;
};

export const safePost = async (response: Response):Promise<IPostStatusResponse | void> => {
  const { content, message } = await response.json();

  if (response.ok && response.status === 201) {
    showInfo(message);

    return content;
  }
};

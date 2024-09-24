import { LikeInfoState } from 'breezeTypesLikes';
import { PermissionsContextType } from 'breezeTypesPermissions';
import { StatusListType } from 'breezeTypesStatus';

import { showError } from '../../utils/tooltip';

export interface IFetchStatus {
  data: StatusListType,
  permissions: PermissionsContextType,
  total: number
}

export const resolveGet = async (response: Response):Promise<IFetchStatus | Array<LikeInfoState> | void> => {
  const { content, message } = await response.json();

  if (message.length) {
    showError(message);
  }

  if (response.ok && response.status === 200) {
    return content;
  }
};

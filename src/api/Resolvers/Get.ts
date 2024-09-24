import { LikeInfoState } from 'breezeTypesLikes';
import { IFetchStatus } from 'breezeTypesStatus';

import { showError } from '../../utils/tooltip';


export const resolveGet = async (response: Response):Promise<IFetchStatus | Array<LikeInfoState> | void> => {
  const { content, message } = await response.json();

  if (message.length) {
    showError(message);
  }

  if (response.ok && response.status === 200) {
    return content;
  }
};

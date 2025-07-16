import { LikeType } from 'breezeTypesLikes';

import { userData } from './userData';

const basic:LikeType = {
  userData: userData.basic,
  contentId: 1,
  count: 0,
  type: 'lol',
  likeTime: 'some date',
};
const countMoreThanOne = { ...basic, ...{ count: 2 } };

const custom = (replace: Partial<LikeType>) => {
  return { ...basic, ...replace };
};

const likes = { basic, countMoreThanOne, custom };

export default likes;

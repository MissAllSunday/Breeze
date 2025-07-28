import type { LikeInfoType } from 'breezeTypesLikes';

import { userData } from './userData';

const basic:LikeInfoType = {
  text: 'some text',
  href: 'some href',
  likes: [
    {
      userData: userData.basic,
      contentId: 1,
      count: 0,
      type: 'lol',
      likeTime: 'some date',
    },
  ],
  contentId: 1,
  count: 1,
  type: 'lol',
  alreadyLiked: false,
  canLike: true,
};

const custom = (replace: Partial<LikeInfoType>) => {
  return { ...basic, ...replace };
};

export const likesInfo = { basic, custom };

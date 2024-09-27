import { StatusType } from 'breezeTypesStatus';

import { comments } from './comments';
import likes from './likes';
import { userData } from './userData';

const basic:StatusType = {
  id: 666,
  wallId: 1,
  userId: 666,
  likes: 0,
  body: 'this is a status body content',
  createdAt: 'some date',
  likesInfo: likes.basic,
  comments: [comments.basic],
  userData: userData.basic,
  isNew: true,
};

const custom = (replace: Partial<StatusType>) => {
  return { ...basic, ...replace };
};

export const status = { basic, custom };

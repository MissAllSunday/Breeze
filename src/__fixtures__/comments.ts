import { CommentType } from 'breezeTypesComments';

import likes from './likes';
import { userData } from './userData';


const basic:CommentType = {
  id: 666,
  statusId: 666,
  userId: 0,
  likes: 0,
  body: 'this is a basic comment',
  likesInfo: likes.basic,
  createdAt: 'some date',
  userData: userData.basic,
  isNew: true,
};

const custom = (replace: Partial<CommentType>) => {
  return { ...basic, ...replace };
};

export const comments = { basic, custom };

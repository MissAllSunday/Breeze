import { LikeType } from 'breezeTypesLikes';

const basic:LikeType = {
  additionalInfo: {
    text: 'some text',
    href: 'https://missallsunday.com',
  },
  alreadyLiked: false,
  canLike: true,
  contentId: 1,
  count: 0,
  type: 'lol',
};
const alreadyLiked = { ...basic, ...{ alreadyLiked: true } };
const cannotLike = { ...basic, ...{ canLike: false } };
const countMoreThanOne = { ...basic, ...{ count: 2 } };

const custom = (replace: Partial<LikeType>) => {
  return { ...basic, ...replace };
};

const likes = { basic, alreadyLiked, cannotLike, countMoreThanOne, custom };

export default likes;

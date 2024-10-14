import '@testing-library/jest-dom';

import { render, screen } from '@testing-library/react';
import { LikeType } from 'breezeTypesLikes';
import React from 'react';

import { LikeInfo } from './LikeInfo';

const MOCK_LIKE_ITEM = {
  additionalInfo: {
    text: 'lol',
    href: 'lol',
  },
  alreadyLiked: false,
  canLike: true,
  contentId: 1,
  count: 0,
  type: 'lol',
};

function act(overwriteLikeItemTo?: Partial<LikeType>) {

  const likeItem:LikeType = { ...MOCK_LIKE_ITEM, ...overwriteLikeItemTo };

  return render(<LikeInfo item={likeItem} />);
}

describe('When there are no likes', () => {
  it('shows default 0 likes text', () => {

    act();

    const spanElement = screen.queryByTestId('likesInfo');
    expect(spanElement).not.toBeInTheDocument();
  });
});

describe('When there are likes', () => {
  it('shows number of likes', () => {

    act({ count:2 });

    const spanElement = screen.queryByTestId('likesInfo');
    expect(spanElement).toBeInTheDocument();
  });
});

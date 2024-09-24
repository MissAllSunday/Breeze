import '@testing-library/jest-dom';

import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';

import { LikeInfo } from './LikeInfo';

describe('LikeInfo', () => {
  it('renders default info', () => {
    const likeItem = {
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

    const likeInfoComponent = render(<LikeInfo item={likeItem} />);
    const ulElement = screen.getByTestId('likes');

    expect(ulElement).toBeEmptyDOMElement();
    expect(likeInfoComponent.getByText(`${String.fromCodePoint(128077)} lol`)).toBeInTheDocument();
  });
});

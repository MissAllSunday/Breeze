import '@testing-library/jest-dom';

import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';

import { Like } from './Like';

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
jest.mock('./LikeInfo', () => ({ LikeInfo: () => 'mocked like info' }));
jest.mock('../api/LikeApi', () => ({
  postLike: () => new Promise((resolve, reject) => {
    process.nextTick(() =>
      resolve({
        content: likeItem,
        message: '',
      }),
    );
  }),
}));

describe('Like', () => {
  it('has already been liked', async () => {
    window.confirm = () => true;

    render(<Like item={likeItem} />);

    const spanElement = screen.getByTitle('lol');
    expect(spanElement).toBeInTheDocument();

    await waitFor(() => {
      userEvent.click(spanElement);
    });

    await waitFor(() => expect(spanElement).toHaveTextContent(String.fromCodePoint(128077)));
  });
});

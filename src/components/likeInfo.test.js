import '@testing-library/jest-dom';

import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';

import * as LikeApi from '../api/LikeApi';
import {LikeInfo } from './LikeInfo';

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

  it('clicked detailed info', async () => {
    const likeItem = {
      additionalInfo: {
        text: 'lol',
        href: 'lol',
      },
      alreadyLiked: false,
      canLike: true,
      contentId: 1,
      count: 1,
      type: 'lol',
    };
    const returnedInfo = [
      {
        profile: {
          avatar: {
            href: 'url',
            image: '<img />',
            name: 'avatar',
            url: 'url',
          },
          id: 1,
          last_login_timestamp: 'timestamp',
          link: 'link',
          link_color: 'link_color',
          name: 'name',
          name_color: 'name_color',
        },
        timestamp: 'timestamp',
      },
    ];

    const getLikeInfo = jest.spyOn(LikeApi, 'getLikeInfo').mockImplementation(() => Promise.resolve().then(() => returnedInfo));
    const likeInfoComponent = render(<LikeInfo item={likeItem} />);
    const liElement = likeInfoComponent.getByTestId('likesDetails');
    const spanElement = likeInfoComponent.getByTestId('likesInfo');

    await waitFor(() => {
      userEvent.click(spanElement);
    });

    await waitFor(() => expect(getLikeInfo).toHaveBeenCalled());
    await waitFor(() => expect(getLikeInfo).toHaveBeenCalledWith(likeItem));
    // await waitFor(() => expect(liElement).toBeInTheDocument())

    expect(likeInfoComponent.getByText(`${String.fromCodePoint(128077)} lol`)).toBeInTheDocument();

    likeInfoComponent.debug();
  });
});

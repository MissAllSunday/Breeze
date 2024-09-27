import '@testing-library/jest-dom';

import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { LikeType } from 'breezeTypesLikes';
import { PermissionsContextType } from 'breezeTypesPermissions';
import React from 'react';

import likes from '../__fixtures__/likes';
import permissions from '../__fixtures__/permissions';
import { PermissionsContext } from '../context/PermissionsContext';
import { Like } from './Like';

const MOCK_LIKE_ITEM = likes.basic;

jest.mock('./LikeInfo', () => ({ LikeInfo: () => 'mocked like info' }));
jest.mock('../api/Like/Post', ()=> jest.fn());

function act(setPermissionsTo: boolean, overwriteLikeItem?: Partial<LikeType>) {

  const permissionsPartial:Partial<PermissionsContextType> = {
    isEnable: { enableLikes: setPermissionsTo },
    Forum: {
      likesLike: setPermissionsTo,
      adminForum: false,
      profileView: false,
    },
  };
  const customPermissions:PermissionsContextType = { ...permissions.basic, ...permissionsPartial };
  const likeItem:LikeType = { ...MOCK_LIKE_ITEM, ...overwriteLikeItem };

  return render(<PermissionsContext.Provider value={customPermissions}>
    <Like item={likeItem} />
  </PermissionsContext.Provider>);
}

beforeAll(()=> {
  window.confirm = jest.fn();
});

describe('When like setting is disable and permissions are not granted', () => {
  it('does not render the Like component', async () => {
    act(false);
    const spanElement = screen.queryByTitle(MOCK_LIKE_ITEM.additionalInfo.text);
    expect(spanElement).not.toBeInTheDocument();
  });
});

describe('When like setting is enable and permissions are granted', () => {
  it('render the Like component', async () => {
    const { container } = act(true);

    expect(container.firstChild).toHaveClass('smflikebutton');
  });

  it('shows span tag', async () => {
    const { container } = act(true);

    const spanElement = screen.queryByTitle(MOCK_LIKE_ITEM.additionalInfo.text);
    expect(spanElement).toBeInTheDocument();
  });

  describe('When the user likes something', () => {
    it('text changes to liked', async () => {
      act(true);
      const spanElement = screen.getByTitle(MOCK_LIKE_ITEM.additionalInfo.text);

      await waitFor(() => {
        userEvent.click(spanElement);
      });

      await waitFor(() => expect(spanElement).toHaveTextContent(String.fromCodePoint(128077)));
    });
  });
});
